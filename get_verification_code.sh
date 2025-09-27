#!/bin/bash

# Simple script to get verification code for testing purposes
# Usage: ./get_verification_code.sh user@example.com

if [ $# -eq 0 ]; then
    echo "Usage: $0 <email>"
    exit 1
fi

EMAIL=$1

# Validate email format
if [[ ! $EMAIL =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
    echo "Invalid email format"
    exit 1
fi

echo "Getting verification code for: $EMAIL"

# Make API request to get verification code
curl -X POST http://localhost:8000/api/get-verification-code \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\": \"$EMAIL\"}" | jq

echo ""