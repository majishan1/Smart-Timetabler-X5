#!/usr/bin/env python3
import sys
import requests
from icalendar import Calendar, Event

if len(sys.argv) < 2:
    print('No url provided')
    sys.exit(1)

url = sys.argv[1]

try:
    data = requests.get(url)
    data.raise_for_status()


    cal = Calendar.from_ical(data.text)

    for item in cal.walk():
        if item.name == "VEVENT":
            print(f'Event: {item.get("summary")}\n')
            print(f'Starts: {item.get("dtstart").dt}\n')


except Exception as e:
    print('Error raised:', e)