# Course statistics

Definitions of the fields collected in course statistics.

| Field | Description |
| --- | --- |
| **id** | Unique ID for this statistic |
| **timestamp** | When this statistic was collected |
| **course[id]** | Canvas course ID |
| **course[name]** | Human-readable course name, as listed in Canvas |
| **course[account_id]** | The account_id associated with this course (i.e. the department) for departmental aggregation of data. |
| **account[name]** | Human-readable department name |
| **teacher[id]s** | A serialized array of Canvas user IDs for the teacher(s) of the course. |
| **teacher[sortable_name]s** | Serialized list of human-readable teacher names |
| **assignments_due_count** | The overall number of assignments with due dates prior to the timestamp, including both graded and ungraded assignments and zero-point assignments. |
| **dateless_assignment_count** | Assignments that lack due dates. |
| **average_assignment_lead_time** | The average amount of time, in days, between when the assignment was created and when it is due. |
| **created_after_due_count** | The number of assignments that were entered into Canvas after their due date (i.e. retroactively) |
| **created_modified_histogram** | A histogram showing when assignments have been created an edited throughout the day. |
| **gradeable_assignment_count** | The number of gradeable, non-zero-point, assignments posted in this course with due dates prior to this statistic collection timestamp |
| **graded_assignment_count** | The number of graded, non-zero-point assignments with due dates prior to the timstamp of this statistic for which at least one submission has been graded |
| **oldest_ungraded_assignment_due_date** | Due date of the oldest graded, non-zero-point assignment due prior to the timestamp of this statistic for which no submissions have grades entered. |
| **oldest_ungraded_assignment_url** | URL of the oldest ungraded assignment |
| **oldest_ungraded_assignment_name** | Human-readable name of the oldest ungraded assignment |
| **average_grading_turn_around** | Of those assignments that were due prior to the timestamp of this statistic for which at least one submission has been graded, what is the average turn-around time (in days) for those submission grades? |
| **zero_point_assignment_count** | Many zero-point assignments suggest that the teacher is not using the "not graded" option |
| **average_submissions_graded** | Of assignments due prior to this statistic timestamp, for which at least one submission has been graded, what percentage of the overall submissions for each assignment have been graded? |
| **gradebook_url** | URL of the course gradebook |
| **student_count** | The number of student enrollments in this course. (Not yet filtering out the Test Student enrollments.) |
| **analytics_page** | URL of the course grading analytics page |
