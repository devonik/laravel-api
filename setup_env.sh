#!/bin/sh

{
  echo "APP_NAME='$APP_NAME'"
  echo "APP_ENV='$APP_ENV'"
  echo "APP_KEY='$APP_KEY'"
  echo "APP_DEBUG='$APP_DEBUG'"
  echo "MC_KEY='$MC_KEY'"
} >> .env
