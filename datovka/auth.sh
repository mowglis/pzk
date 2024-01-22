#!/bin/bash
if [ "$PAM_TYPE" == "auth" ] && \
[ "$PAM_USER" == "datovka" ] && \
[ "$PAM_RUSER" == "apache" ]; then
  exit 0
else
  exit 1
fi
