#!/bin/sh
# Script to test the coding standard on github pull request
# The first argument is to the tool to use to check the CS, the
# following arguments depend on the choosen tool.
#
# Requirements in the path:
# * standard tools: find, wc, rm, grep, wget
# * external tools: php, phpcs, jshint, csslint, yuidoc
#
# Arguments for PHP:
# phpcs -n --extensions=php --ignore='*/_fixtures/*' --standard=ezcs eZ/
# 
# Arguments for JavaScript:
# jshint "<list files or directories separated by space to search for .js file>" --verbose
#
# Arguments for CSS:
# csslint --format=compact Resouces/public/css/*css
#
# Arguments to check the JavaScript API doc:
# yuidoc <list of directories separated by space to search for .js files>

EXIT_CODE=0
REPORT="logs/report.txt"
REPO=$1
shift

TOOL=$1
shift

REMOTE_CSSLINTRC=https://raw.github.com/ezsystems/ezcs/master/css/csslintrc
CSSLINTRC=.csslintrc


setCommitStatus.php "$REPO" $(git rev-parse HEAD) "pending" "" "Code review by ezrobot" "ezrobot"

if [ "$TOOL" = "phpcs" ] ; then
    phpcs --report-full="$REPORT" $*
    EXIT_CODE=$?
    if [ $EXIT_CODE -ne 0 ] ; then
        sed -i '1s@^@This Pull Request does not respect our [PHP Coding Standards](https://github.com/ezsystems/ezcs/tree/master/php), please, see the report below:\n\n```\n@' "$REPORT"
        echo '```' >> "$REPORT"
    fi
elif [ "$TOOL" = "jshint" ] ; then
    SRC=$1
    shift
    for f in `find $SRC -iname \*.js` ; do
        OUT=`jshint $* "$f" 2>&1`
        LOCAL_EXIT_CODE=$?
        [ $LOCAL_EXIT_CODE -ne 0 ] && echo "\`\`\`\n$OUT\n\`\`\`\n" >> "$REPORT" && EXIT_CODE=42
    done
    if [ $EXIT_CODE -ne 0 ] ; then
        sed -i '1s@^@jshint with [our configuration](https://github.com/ezsystems/ezcs/tree/master/js) reports the following issues:\n\n@' "$REPORT"
    fi
elif [ "$TOOL" = "csslint" ] ; then
    [ ! -f "$CSSLINTRC" ] && wget "$REMOTE_CSSLINTRC" -O "$CSSLINTRC"
    csslint $* | grep --color=never 'Error' > "$REPORT"
    EXIT_CODE=`wc -l $REPORT | cut -d ' ' -f 1`
    if [ $EXIT_CODE -ne 0 ] ; then
        sed -i '1s@^@csslint with [our configuration](https://github.com/ezsystems/ezcs/tree/master/css) reports the following errors:\n\n```\n@' "$REPORT"
        echo '```' >> "$REPORT"
    fi
elif [ "$TOOL" = "yuidoc" ] ; then
    yuidoc --lint $* >> $REPORT
    EXIT_CODE=$?
    if [ $EXIT_CODE -ne 0 ] ; then
        sed -i '1s@^@yuidoc reports the following documentation warnings:\n\n```\n@' "$REPORT"
        echo '```' >> "$REPORT"
    fi
fi

# Output the report for easier debug in case of (gihub/*) issues
cat $REPORT

if [ $EXIT_CODE -ne 0 ] ; then
    postComment.php "$REPO" $(grep -l $(git rev-parse HEAD) .git/refs/remotes/origin/pr/*/head | sed 's@\.git/refs/remotes/origin/pr/@@;s@/head@@') "$PWD/$REPORT"
    setCommitStatus.php "$REPO" $(git rev-parse HEAD) "failure" "" "Code review by ezrobot" "ezrobot"
else
    setCommitStatus.php "$REPO" $(git rev-parse HEAD) "success" "" "Code review by ezrobot" "ezrobot"
fi

[ -f "$REPORT" ] && rm "$REPORT"
exit 0
