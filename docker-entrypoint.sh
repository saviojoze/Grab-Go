#!/bin/bash
set -e

# Fix "More than one MPM loaded" error by explicitly disabling mpm_event
# and enabling mpm_prefork at runtime before starting Apache.
echo "Configuring Apache MPM..."
a2dismod mpm_event || true
a2enmod mpm_prefork || true

# Execute the original Apache foreground command
exec apache2-foreground
