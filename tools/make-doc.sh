BRANCH="$(git branch | grep \* | cut -d ' ' -f2)"
git pull origin master
git checkout master
git checkout $BRANCH

curl -O https://get.sensiolabs.org/sami.phar
php sami.phar update tools/sami.php

mkdir -p site
mv doc site/doc
