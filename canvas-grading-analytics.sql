# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.40-0ubuntu0.12.04.1)
# Database: canvas-grading-analytics
# Generation Time: 2014-11-29 03:15:19 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table course_statistics
# ------------------------------------------------------------

CREATE TABLE `course_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for this statistic',
  `timestamp` text NOT NULL COMMENT 'When this statistic was collected',
  `course[id]` int(11) NOT NULL COMMENT 'Canvas course ID',
  `course[name]` text NOT NULL COMMENT 'Human-readable course name, as listed in Canvas',
  `course[account_id]` int(11) NOT NULL COMMENT 'The account_id associated with this course (i.e. the department) for departmental aggregation of data.',
  `account[name]` text NOT NULL COMMENT 'Human-readable department name',
  `teacher[id]s` text NOT NULL COMMENT 'A serialized array of Canvas user IDs for the teacher(s) of the course.',
  `teacher[sortable_name]s` text NOT NULL COMMENT 'Serialized list of human-readable teacher names',
  `assignments_due_count` int(11) NOT NULL COMMENT 'The overall number of assignments with due dates prior to the timestamp, including both graded and ungraded assignments and zero-point assignments.',
  `dateless_assignment_count` int(11) NOT NULL COMMENT 'Assignments that lack due dates.',
  `average_assignment_lead_time` float NOT NULL COMMENT 'The average amount of time, in days, between when the assignment was created and when it is due.',
  `created_after_due_count` int(11) NOT NULL COMMENT 'The number of assignments that were entered into Canvas after their due date (i.e. retroactively)',
  `created_modified_histogram` text NOT NULL COMMENT 'A histogram showing when assignments have been created an edited throughout the day.',
  `gradeable_assignment_count` int(11) NOT NULL COMMENT 'The number of gradeable, non-zero-point, assignments posted in this course with due dates prior to this statistic collection timestamp',
  `graded_assignment_count` int(11) NOT NULL COMMENT 'The number of graded, non-zero-point assignments with due dates prior to the timstamp of this statistic for which at least one submission has been graded',
  `oldest_ungraded_assignment_due_date` text NOT NULL COMMENT 'Due date of the oldest graded, non-zero-point assignment due prior to the timestamp of this statistic for which no submissions have grades entered.',
  `oldest_ungraded_assignment_url` text NOT NULL COMMENT 'URL of the oldest ungraded assignment',
  `oldest_ungraded_assignment_name` text NOT NULL COMMENT 'Human-readable name of the oldest ungraded assignment',
  `average_grading_turn_around` text NOT NULL COMMENT 'Of those assignments that were due prior to the timestamp of this statistic for which at least one submission has been graded, what is the average turn-around time (in days) for those submission grades?',
  `zero_point_assignment_count` int(11) NOT NULL COMMENT 'Many zero-point assignments suggest that the teacher is not using the "not graded" option',
  `average_submissions_graded` float NOT NULL COMMENT 'Of assignments due prior to this statistic timestamp, for which at least one submission has been graded, what percentage of the overall submissions for each assignment have been graded?',
  `gradebook_url` text NOT NULL COMMENT 'URL of the course gradebook',
  `student_count` int(11) NOT NULL COMMENT 'The number of student enrollments in this course. (Not yet filtering out the Test Student enrollments.)',
  `analytics_page` text NOT NULL COMMENT 'URL of the course grading analytics page',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
