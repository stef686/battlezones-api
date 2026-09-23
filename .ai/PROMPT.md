1. Find the highest-priority issue marked as ready-for-agent on this project's Github. This should be the one YOU decide has the highest priority – not
   necessarily the first in the list.
   Respect the issue's "Blocked by" section: do not start an issue while any of its blockers are still open, however small it looks.
2. Set the status of the issue on the Battlezones Dev project to In progress before starting any work
3. Branch off main for the issue. Do not commit to main.
4. Work on the issue with /tdd
5. Feedback loops, such as types and tests, will be run on commit.
6. Update the issue leaving a comment summarising the work done, remove the ready-for-agent label, add the QA label, and set the status on the Battlezones Dev project to QA
7. Commit your changes and open a pull request against main, referencing the issue.

ONLY WORK ON A SINGLE ISSUE.

If there are no more ready-for-agent issues, output <promise>COMPLETE</promise>.
