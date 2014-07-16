Canvas ICS Sync
===============

A pair (trio?) of tools for working with Canvas and ICS feeds. There is an export tool that exposes the pre-existing ICS feed for course calendars and there is an import tool that pairs an ICS feed with (theoretically) a course, group or user in Canvas and imports all of the ICS events into that calendar, deleting any residual events created by prior imports of that pairing. The quasi-third tool, a sync tool, is really just a wrapper for using crontab to trigger regular re-imports of an ICS feed pairing.

Some care has been taken to protect privacy by not caching the actual calendar events in our MySQL database cache of ICS/Canvas pairings, but, of course, potentially private information is passing through third party hands, etc., etc.

This would benefit from an OAuth setup, so that individual users could set up their own pairings. However, at the moment, it requires administrative intervention and relies on a single API user, Calendar API Process, to handle all imports. The API user is an admin on our main account.

