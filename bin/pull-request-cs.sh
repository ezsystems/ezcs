#!/bin/sh
# Script to test the coding standard on github pull request
# The first argument is to the tool to use to check the CS, the
# following arguments depend on the choosen tool.
#
# Arguments for PHP:
# phpcs -n --extensions=php --ignore='*/_fixtures/*' --standard=ezcs eZ/
# 
# Arguments for JavaScript:
# jshint "<list or directories separated by space to search for .js file>" --verbose

EXIT_CODE=0
REPORT="logs/report.txt"
TOOL=$1

shift

if [ "$TOOL" = "phpcs" ] ; then
    phpcs --report-full="$REPORT" $*
    EXIT_CODE=$?
elif [ "$TOOL" = "jshint" ] ; then
    SRC=$1
    shift
    for f in `find $SRC -iname \*.js -print` ; do
        jshint $* "$f" 2>&1 >> $REPORT
        LOCAL_EXIT_CODE=$?
        [ $LOCAL_EXIT_CODE -ne 0 ] && echo "--------" >> $REPORT
        EXIT_CODE=`expr $EXIT_CODE + $LOCAL_EXIT_CODE`
    done
fi


if [ $EXIT_CODE -ne 0 ] ; then
    postComment.php $(grep -l $(git rev-parse HEAD) .git/refs/remotes/origin/pr/*/head | sed 's@\.git/refs/remotes/origin/pr/@@;s@/head@@') "$PWD/$REPORT"
fi

[ -f "$REPORT" ] && rm "$REPORT"
