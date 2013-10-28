#!/bin/sh
phpcs --report-full=logs/report.txt $*
EXIT_CODE="$?"
if [ $EXIT_CODE -eq "1" ]; then
  postComment.php $(grep -l $(git rev-parse HEAD) .git/refs/remotes/origin/pr/*/head | sed 's@\.git/refs/remotes/origin/pr/@@;s@/head@@') $PWD/logs/report.txt
fi
