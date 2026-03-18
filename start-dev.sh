#!/bin/bash

# Create a transcripts folder if it doesn't exist
mkdir -p transcripts

# Create a filename with the current date and time
# Example: transcripts/session_2026-03-18_14-30.txt
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M")
LOG_FILE="transcripts/session_$TIMESTAMP.txt"

echo "------------------------------------------------"
echo "Starting MediaVault Development Session"
echo "Transcript logging to: $LOG_FILE"
echo "------------------------------------------------"

# Run Gemini CLI and pipe EVERYTHING to the log file and the screen
# The -i flag ensures the terminal stays interactive
gemini | tee -a "$LOG_FILE"
