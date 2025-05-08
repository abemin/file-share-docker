#!/bin/bash
# Set permissions for /mnt/share if it exists
if [ -d "/mnt/share" ]; then
    chown -R www-data:www-data /mnt/share
    chmod -R 755 /mnt/share
else
    echo "Warning: /mnt/share does not exist. Ensure volume is mounted."
fi

# Start Apache
exec apache2-foreground
