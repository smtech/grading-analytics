{extends file="page.tpl"}

{block name="content"}

	<div class="container">
		<div class="page-header">
			<h1>Grading Analytics <small>{$statistic['course[name]']}</small></h1>
		</div>
	</div>


	<div class="container">
		<div class="readable-width">
			

			<div id="introduction">		
				<p>This summary is provided as a means towards starting a conversation, rather than as a final assessment of work done. As you look at this data, be cognizant of the fact that it reflects specific interactions with Canvas and is <i>not</i> direct evidence of either teaching quality or teacher-student interactions. Where possible, relevant questions have been posed with the data for reflective consideration.</p>
				
				<p>Click on any graph to zoom for fuller detail.</p>
				
				<p>Data collection for these analytics is done at midnight every night, so statistics do not yet reflect grading done today.</p>
			</div>
			
			<div id="turn-around-comparison">
				
				<h3>Grading Turn-Around Time Comparison</h3>
				
				<div class="image-placement">
					<h4>All Courses</h4>
					<a
						data-lightbox="turn-around-comparison"
						title="The average grading turn-around time in &ldquo;{$statistic['course[name]']}&rdquo; compared to all active courses in the school."
						href="graph/turn-around-comparison.php?course_id={$statistic['course[id]']}"
					>
						<img src="graph/turn-around-comparison.php?course_id={$statistic['course[id]']}"  style="width: 100%;" />
						<p class="caption">&ldquo;{$statistic['course[name]']}&rdquo; compared to all active courses in the school.</p>
					</a>
				</div>
				
				<div class="image-placement" style="float: right; width: {$smarty.const.GRAPH_INSET_WIDTH};">
					<h4>{$statistic['account[name]']} Courses</h4>
					<a
						data-lightbox="turn-around-comparison"
						title="The average grading turn-around time in &ldquo;{$statistic['course[name]']}&rdquo; compared to all active courses in the {$statistic['account[name]']} department."
						href="graph/turn-around-comparison.php?course_id={$statistic['course[id]']}&department_id={$statistic['course[account_id]']}"
					>
						<img src="graph/turn-around-comparison.php?course_id={$statistic['course[id]']}&department_id={$statistic['course[account_id]']}"  style="width: 100%;" />
						<p class="caption">&ldquo;{$statistic['course[name]']}&rdquo; compared to all active courses in the{$statistic['account[name]']} department.</p>
					</a>
				</div>
				
				<p class="caption">What is the average turn-around time (in days) in this course for an assignment, from due date to posted grade in Canvas? The expectation articulated in the faculty handbook is that daily assignments will be returned within a week (<span class="one-week-underline">{$smarty.const.GRAPH_1_WEEK_STYLE} {$smarty.const.GRAPH_1_WEEK_COLOR} line</span>) and that major assignments will be returned within two weeks (<span class="two-week-underline">{$smarty.const.GRAPH_2_WEEK_STYLE} {$smarty.const.GRAPH_2_WEEK_COLOR} line</span>). The expectation is that grades will be posted to Canvas at approximately the same time as they are returned to students.</p>
				
				<p class="caption">The average turn-around time across all courses (<span class="average-underline">{$smarty.const.GRAPH_AVERAGE_STYLE} {$smarty.const.GRAPH_AVERAGE_COLOR} line</span> above), weighted by number of graded assignments and students, is {math equation="round(n, 1)" n=$averageTurnAround} days. The average turn-around time across {$statistic['account[name]']} courses (<span class="average-underline">{$smarty.const.GRAPH_AVERAGE_STYLE} {$smarty.const.GRAPH_AVERAGE_COLOR} line</span> at right) is {math equation="round(n, 1)" n=$averageTurnAroundDepartment} days. In &ldquo;{$statistic['course[name]']}&rdquo; (<span class="highlight-column"></span>{$smarty.const.GRAPH_HIGHLIGHT_COLOR} column), the current average is {math equation="round(n, 1)" n=$statistic['average_grading_turn_around']} days.</p>
				
				<h4>What can be learned from this information?</h4>
				
				<ul>
					<li>How does this course&rsquo;s grading turn-around time compare to other active courses?</li>
					
					<li>Is the turn-around time shorter? This may reflect either more prompt feedback to students or fewer complex assignments. It may also reflect due dates being added to assignments after they have already been posted.</li>
					
					<li>Is the turn-around time longer? This may reflect either slower feedback to students, more complex assignments, or a disconnect between when grades are shared with students physically and when they are posted to Canvas.</li>
				</ul>
				<br clear="all" />
			</div>
		
			<div id="turn-around-history">
				
				<h3>Grading Turn-Around History</h3>
				
				<div class="image-placement">
					<a
						data-lightbox="turn-around-history"
						title="The average grading turn-around time in &ldquo;{$statistic['course[name]']}&rdquo; over time."
						href="graph/turn-around-history.php?course_id={$statistic['course[id]']}"
					>
						<img src="graph/turn-around-history.php?course_id={$statistic['course[id]']}" width="100%" />
						<p class="caption">The average grading turn-around time in &ldquo;{$statistic['course[name]']}&rdquo; over time.</p>
					</a>
				</div>
				
				<p class="caption">How is the average grading turn-around time changing in this course over time? Both the daily assignment expectation (<span class="one-week-underline">{$smarty.const.GRAPH_1_WEEK_STYLE} {$smarty.const.GRAPH_1_WEEK_COLOR} line</span>) and the major assignment expectation (<span class="two-week-underline">{$smarty.const.GRAPH_2_WEEK_STYLE} {$smarty.const.GRAPH_2_WEEK_COLOR} line</span>) are shown for reference.</p>
				
				<h4>What can be learned from this information?</h4>
				
				<ul>
					<li>How is this average changing over time? Be aware that it will change more gradually as the year progresses and more assignments are incorporated into the average.</li>
					
					<li>How does the average relate to the expectations for faculty grading? Again, be aware that the average grading turn-around time is a measure not of the actual turn-in to student time, but of the time measured between the due date and when the grade is actually entered in Canvas.</li>
				</ul>
			</div>
			
			<div id="assignment-creation">
				
				<h3>When are assignments posted?</h3>
				
				<div class="image-placement">
					<a
						data-lightbox="assignment-creation"
						title="The times of day when assignments are created and modified for &ldquo;{$statistic['course[name]']}.&rdquo;"
						href="graph/created-modified-histogram.php?course_id={$statistic['course[id]']}"
					>
						<img src="graph/created-modified-histogram.php?course_id={$statistic['course[id]']}" width="100%" />
						<p class="caption">The times of day when assignments are created and modified for &ldquo;{$statistic['course[name]']}.&rdquo;</p>
					</a>
				</div>
				
				<p class="caption">When are assignments  posted to Canvas for &ldquo;{$statistic['course[name]']}&rdquo;? This histogram represents when during the course of the day, assignments have generally been created or modified for the course. The <span class="highlight-column"></span>{$smarty.const.GRAPH_HIGHLIGHT_COLOR} columns represent numbers of assignments created at a given hour during the day, while the <span class="data-column"></span>{$smarty.const.GRAPH_DATA_COLOR} columns represent the numbers of assignment edits made at a given hour during the day.</p>
					
				<h4>What can be learned from this information?</h4>
				
				<ul>
					<li>Note that the modification times are only the most recent modifications to each assignment (so if many modifications were made to a particular assignment, only the last one would show on the histogram).</li>
					<li>Are assignments being created during evening study hours (or with good lead time before evening study hours?) If students have a short lead-time on assignments and many assignments are being created during the evening study hours, this could indicate that assignments are not being posted in a timely manner. (A longer average lead-time might suggest that a teacher is using evening study hours to work ahead.)</li>
					<li>Are assignments being edited frequently during evening study hours? This might suggest incomplete or incorrect assignments are being posted earlier in the day and being edited later. As with assignment creation times, however, it may also indicate a teacher working ahead.</li>
				</ul>
				
				<h3>{math equation="round(n, 1)" n=$statistic['average_assignment_lead_time']} days of lead-time</h4>
				
					<div class="image-placement">
						<p class="caption">In &ldquo;{$statistic['course[name]']},&rdquo; students have, on average, {math equation="round(n, 1)" n=$statistic['average_assignment_lead_time']} days notice between when an assignment is posted and when it is due.</p>
					</a>
				</div>
			
				<h4>What could be this mean?</h4>
			
				<p>A longer average lead-time suggests a teacher who is giving students more advance notice on assignments than a shorter average lead time. This might be because the teacher with a longer average lead-time is giving more involved assignments that extend over more time for the students. It may also be that the teacher with a shorter lead-time is posting assignments based on progress achieved in class that day.</p>
				
				<p>Lead-time should, of course, always be a positive number -- negative average lead-time would mean that many assignments are being posted to Canvas after they are due. There is not clear model for what the ideal lead-time might be, as that shape of assignments varies greatly from discipline to discipline and level to level.</p>
					
				<h3>{$statistic['created_after_due_count']} retroactive assignments</h4>
				
					<div class="image-placement">
						<p class="caption">{$statistic['created_after_due_count']} assignments have been created <i>after</i> their due dates in &ldquo;{$statistic['course[name]']}.&rdquo;</p>
					</a>
				</div>
			
				<h4>What could be this mean?</h4>
			
				<p>These are assignments that were entered into Canvas after their due dates, which means that students had no advance notice of the assignments via Canvas. This could mean any number of things: a pop quiz that was entered when it was graded, extra credit on a test that was added during grading as a separate assignment, <a href="http://helpdesk.stmarksschool.org/blog/window-grades-as-snapshots-in-canvas/">a &ldquo;reporting&rdquo; assignment</a> created to update students on a particular grade, or an assignment that was not entered into Canvas until after it was due.</p>
				<br clear="all" />
			</div>
		
			<div id="assignment-count">
				
				<h3>Number of Assignments</h3>
				
				<div class="image-placement">
					<h4>All Courses</h4>
					<a
						data-lightbox="assignment-count"
						title="The number of assignments posted in Canvas for &ldquo;{$statistic['course[name]']}&rdquo; compared to all active courses in the school."
						href="graph/assignment-count-comparison.php?course_id={$statistic['course[id]']}"
					>
						<img src="graph/assignment-count-comparison.php?course_id={$statistic['course[id]']}" width="100%" />
						<p class="caption">&ldquo;{$statistic['course[name]']}&rdquo; compared to all active courses in the school.</p>
					</a>
				</div>
			
				<div class="image-placement" style="width: {$smarty.const.GRAPH_INSET_WIDTH}; float: right;">
					<h4>{$statistic['account[name]']} Courses</h4>
					<a
						data-lightbox="assignment-count"
						title="The number of assignments posted in Canvas for &ldquo;{$statistic['course[name]']}&rdquo; compared to all active courses in the {$statistic['account[name]']} department."
						href="graph/assignment-count-comparison.php?course_id={$statistic['course[id]']}&department_id={$statistic['course[account_id]']}"
					>
						<img src="graph/assignment-count-comparison.php?course_id={$statistic['course[id]']}&department_id={$statistic['course[account_id]']}" width="100%" />
						<p class="caption">&ldquo;{$statistic['course[name]']}&rdquo; compared to all active courses in the {$statistic['account[name]']} department.</p>
					</a>
				</div>
				
				<p class="caption">How many assignments have been posted to Canvas for &ldquo;{$statistic['course[name]']}&rdquo;?</p>
				
				<p class="caption">The average number of assignments posted to Canvas (<span class="average-underline">{$smarty.const.GRAPH_AVERAGE_STYLE} {$smarty.const.GRAPH_AVERAGE_COLOR} line</span> above) across all courses is {math equation="round(n, 1)" n=$averageAssignmentCount}. The average number of assignments posted to Canvas (<span class="average-underline">{$smarty.const.GRAPH_AVERAGE_STYLE} {$smarty.const.GRAPH_AVERAGE_COLOR} line</span> at right) in the {$statistic['account[name]']} department is {math equation="round(n, 1)" n=$averageAssignmentCountDepartment}. In &ldquo;{$statistic['course[name]']}&rdquo; (<span class="highlight-column"></span>{$smarty.const.GRAPH_HIGHLIGHT_COLOR} column), there are {$statistic['assignments_due_count'] + $statistic['dateless_assignment_count']} assignments posted to Canvas.</p>
				
				<h4>What can be learned from this information?</h4>
				
				<ul>
					<li>How do the number of assignments posted to Canvas in this course compare to other courses? This is probably more illuminating in the departmental comparison than in the overall comparison.</li>
					
					<li>Is this number higher than most? This may reflect either a higher homework load (at least, measured by quantity of assignments), or it may reflect a greater granularity in posted assignments. That is, rather than combining all of one night&rsquo;s homework into a single assignment, homework may be posted as a number of individual assignments.</li>
					
					<li>Is this number lower than most? This may reflect a lighter homework load (at least, measured by quantity of assignments -- each individual assignment may take longer). This may also reflect incomplete Canvas updates in this course, with not all assignments posted to Canvas.</li>
					
					<li>How does the number of assignments posted compare to the number of class meetings?</li>
				</ul>
				
				<br clear="all" />
			</div>
			
			<div id="numbers">
				
				<h3>(Potentially) Interesting Numbers</h3>
				
				{if strlen($statistic['oldest_ungraded_assignment_url'])}
					{math equation="round(n, 1)" n=(($smarty.now - strtotime($statistic['oldest_ungraded_assignment_due_date'])) / (60*60*24)) assign="ageInDays"}
					<h4>Oldest ungraded assignment is {$ageInDays} days old</h4>
					<div class="image-placement">
						<p class="caption"><a style="border-bottom: dotted 1px black;" target="_blank" href="{$statistic['oldest_ungraded_assignment_url']}">{$statistic['oldest_ungraded_assignment_name']}</a>, due {$statistic['oldest_ungraded_assignment_due_date']|date_format:'l, F j, Y'} was due {$ageInDays} days ago and no submissions have been graded.</p>
					</div>
							
					<h5>What could this mean?</h5>
					
					<p>This is the oldest assignment (sorted by due date) for which no student submissions have received a grade. This could mean exactly what it appears to mean. It could be an extra credit (or test corrections) assignment. However, it may also mean that this was a zero-point or ungraded assignment &mdash; that is, an assignment that was <a href="http://helpdesk.stmarksschool.org/blog/how-do-i-create-an-ungraded-assignment/" target="_blank">never <i>meant</i> to receive a grade</a> &mdash; that was mismarked.</p>
				{else}
					<h4>No assignment is ungraded</h4>
				{/if}
				
				{if (round($statistic['average_submissions_graded']*100, 0) < 100)}
					{math equation="round(n, 0)" n=($statistic['average_submissions_graded']*100) assign="percentGraded"}
					<h4>{$percentGraded}% of submissions graded</h4>
					
					<div class="image-placement">
						<p class="caption">For each assignment in &ldquo;{$statistic['course[name]']}&rdquo;, on average {$percentGraded}% of the student submissions have been graded.</p>
					</div>
					
					<h5>What could this mean?</h5>
					
					<p>The quick interpretation of this statistic is that {$percentGraded}% of assignments still need to be graded in this class. However, this number may also be influenced by extra credit assignments (for which not all students submitted work) or by the presence of the <a target="_blank" href="https://stmarksschool.instructure.com/courses/489/wiki/who-is-this-test-student">Test Student</a> in the class (who may or may not have turned in work or been graded... and it doesn&rsquo;t particularly matter). This percentage could also be dragged down by zero-point assignments that were <a href="http://helpdesk.stmarksschool.org/blog/how-do-i-create-an-ungraded-assignment/" target="_blank">never meant to be graded</a>, but are not formally marked as ungraded (and therefore appear in the gradebook).</p>
				{else}
					<h4>100% of submissions graded</h4>
				{/if}
				
				{if $statistic['zero_point_assignment_count'] > 0}
					<h4>{$statistic['zero_point_assignment_count']} zero-point assignments</h4>
					
					<div class="image-placement">
						<p class="caption">There are {$statistic['zero_point_assignment_count']} assignments worth zero points in &ldquo;{$statistic['course[name]']}&rdquo;.</p>
					</div>
					
					<h5>What could this mean?</h5>
					
					<p>Zero-point assignments are often (but not <i>always</i>) assignments that were never meant to receive a grade: reading, practice, etc. In this case, the teacher could <a href="http://helpdesk.stmarksschool.org/blog/how-do-i-create-an-ungraded-assignment/" target="_blank">remove this unnecessary column from the gradebook</a> while still preserving it as a to-do item for their students. However, these may also be assignments for which a value has not yet been determined or may represent extra credit assignments or test corrections. It may be worth considering if this could be useful information to support student planning.</p>
				{else}
					<h4>No zero-point assignments</h4>
				{/if}
				
					
				{if $statistic['dateless_assignment_count'] > 0}
					<h4>{$statistic['dateless_assignment_count']} assignments without due dates</h4>
					
					<div class="image-placement">
						<p class="caption">There are {$statistic['dateless_assignment_count']} assignments without due dates in &ldquo;{$statistic['course[name]']}&rdquo;.</p>
					</div>
					
					<h5>What could this mean?</h5>
					
					<p>This pretty much means what it sounds like it means: these assignments lack due dates. In terms of supporting student planning, there may be questions to ask about communication and clarity.</p>
				{else}
					<h4>All assignments have due dates</h4>
				{/if}
			</div>
			
			<div id="questions">
				
				<h3>Questions?</h3>
				
				<p>Do you have any questions about the information shown here? Do you want to see different data that is not presented here? There is lots more information about Canvas, how to use it and how we are trying to use it.</p>
				
				<ul>
					<li><a target="_blank" href="https://stmarksschool.instructure.com/courses/489">Canvas Training</a> course online</li>
					<li><a target="_blank" href="http://guides.instructure.com/">Canvas Guides</a> to just about everything</li>
					<li><a target="_blank" href="http://helpdesk.stmarksschool.org/blog/keyword/canvas/">Tech Tips</a> specific to St. Mark&rsquo;s</li>
					<li><a target="_blank" href="https://stmarksschool.instructure.com/courses/97/wiki/academic-technology">Academic Technology wiki</a> links to lots of other germane information</li>
				</ul>
				
				<p>Please contact Brian or Seth with specific questions, and we will endeavor to explicate, disentangle and otherwise address your concerns.</p>
			</div>
		</div>	
	</div>

{/block}