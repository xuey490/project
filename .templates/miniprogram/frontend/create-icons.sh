#!/bin/bash

# Create minimal PNG icons using base64
# These are 1x1 pixel PNGs - replace with proper icons in production

cd src/assets/images

# Gray icon (1x1 pixel #666666)
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mM0NTX9DwAChQGA48C5bwAAAABJRU5ErkJggg==" | base64 -d > submit.png
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mM0NTX9DwAChQGA48C5bwAAAABJRU5ErkJggg==" | base64 -d > list.png
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mM0NTX9DwAChQGA48C5bwAAAABJRU5ErkJggg==" | base64 -d > stats.png
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mM0NTX9DwAChQGA48C5bwAAAABJRU5ErkJggg==" | base64 -d > profile.png

# Purple/blue icon (1x1 pixel #667eea)
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" | base64 -d > submit-active.png
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" | base64 -d > list-active.png
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" | base64 -d > stats-active.png
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" | base64 -d > profile-active.png

echo "Minimal PNG icons created. Note: These are 1x1 pixel placeholders."
echo "For production, replace with proper 81x81px icons."
