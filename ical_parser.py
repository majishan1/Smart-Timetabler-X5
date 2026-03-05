#!/usr/bin/env python3
import sys
import requests
from icalendar import Calendar

def clean_txt(event, ev_type) -> str:
    """
    Take an icalendar.Calendar VEVENT and return is as a formatted string, instead of using iCal
    defined escapes

    :param event: icalendar object for a given event
    :type event: icalendar.cal.event.event

    :param ev_type: String containing a VEVENT descriptor
    :type ev_type: str
    
    """
    data = event.get(ev_type)
    if data == None:
        return ""
    return str(data)

def clean_date(event, ev_type):
    data = event.get(ev_type)
    if data:
        return data.dt.isoformat()
    return None

clean_txt()
# Make sure there is a URL provided to the program
if len(sys.argv) < 2:
    print("No URL provided")
    sys.exit(1)

url = sys.argv[1]

# Make Sure URL uses valid and safe protocol
if not url.startswith(("http://", "https://")):
    print("Invalid URL protocol Provided")
    sys.exit(1)

try:
    data = requests.get(url, timeout=10)
    data.raise_for_status()
    bit_content = data.content

    if b"BEGIN:VCALENDAR" not in bit_content:
        print("Contents not in proper iCal format")
        sys.exit(1)


    cal = Calendar.from_ical(bit_content)
    event_dict = {}
    for event in cal.walk("VEVENT"):
        print(type(event))
        event_dict[clean_txt(event, 'UID')] = {'summary': clean_txt(event, 'summary'),
                                                'location': clean_txt(event, 'location'),
                                                'dtstart': clean_date(event, 'dtstart'),
                                                'dtend': clean_date(event, 'dtend'),
                                                'dtstamp': clean_date(event, 'dtstamp'),
                                                'description': clean_txt(event, 'description')}


        
except requests.exceptions.RequestException as e:
    print(f"URL could not be fetched: {e}")
except ValueError as e:
    print(f"Content of URL could not be parsed as iCAL: {e}")
except Exception as e:
    print(f"Unknown error: {e}")