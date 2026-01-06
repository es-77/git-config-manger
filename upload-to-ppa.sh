#!/bin/bash
# ==================================================
# Script to build, sign, and upload a Debian package to PPA
# Author: Emmanuel Saleem
# ==================================================

# ---- CONFIGURE ----
PROJECT_DIR="/home/shayan/Desktop/php-dev/destop_app/git-config-manager"
BUILD_DIR="/home/shayan/Desktop/php-dev/destop_app/git-config-manager-debian"
PPA="ppa:emmanuelsaleem/emmanuel-saleem"
DEBFULLNAME="emmanuelsaleem"
DEBEMAIL="emmanuelsaleem098765@gmail.com"
DEBSIGN_KEYID="6075BB5578513B1216F7D2437CEBE08CC07F961C"
DIST="jammy"
PACKAGE="git-config-manager"
VERSION="1.0-1"
CHANGELOG_MSG="Initial release"

# ---- EXPORT ENV ----
export DEBFULLNAME DEBEMAIL DEBSIGN_KEYID

# ---- GO TO PROJECT DIR ----
cd "$PROJECT_DIR" || { echo "Project directory not found!"; exit 1; }

# ---- BACKUP OLD CHANGELOG ----
if [ -f debian/changelog ]; then
    cp debian/changelog debian/changelog.backup
    echo "Old changelog backed up to debian/changelog.backup"
fi

# ---- CREATE NEW CHANGELOG ----
dch --create --package "$PACKAGE" --newversion "$VERSION" --distribution "$DIST" "$CHANGELOG_MSG"

# ---- MAKE RULES EXECUTABLE ----
chmod +x debian/rules

# ---- BUILD SOURCE PACKAGE IN PROJECT DIR ----
debuild -S -us -uc -d

# ---- COPY BUILD TO BUILD_DIR ----
mkdir -p "$BUILD_DIR"
cp ../"${PACKAGE}_${VERSION}_source.changes" "$BUILD_DIR/"
cp ../"${PACKAGE}_${VERSION}_source.buildinfo" "$BUILD_DIR/"
cp ../"${PACKAGE}_${VERSION}.dsc" "$BUILD_DIR/"

echo "Build files copied to: $BUILD_DIR"

# ---- SIGN THE PACKAGE ----
cd "$BUILD_DIR" || exit 1
debsign "${PACKAGE}_${VERSION}_source.changes"

# ---- UPLOAD TO PPA ----
dput "$PPA" "${PACKAGE}_${VERSION}_source.changes"

echo "Package uploaded to PPA: $PPA"
