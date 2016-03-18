{extends file="page.tpl"}

{block name="content"}

	<div class="container">
		<div class="page-header">
			<h1>Grading Analytics <small>{$departments['name']}</small></h1>
			<p>{$statistics[0]['timestamp']|date_format:'F j, Y'} Snapshot</p>
		</div>
	</div>
	
	<div class="container container-fluid">
		<table class="table table-hover table-striped table-condensed sortable">
			<thead>
				<tr>
					<th>Teacher(s)</th>
					<th>Course</th>
					<th>Students</th>
					<th>Turn-Around</th>
					<th>Lead Time</th>
					<th>Graded</th>
					<th>Due</th>
					<th>No Due Dates</th>
					<th>Retroactive Assignments</th>
					<th>Gradable Assignments</th>
					<th>Graded Assignments</th>
					<th data-dateformat="MMM DD, YYYY">Oldest Ungraded Assignment</th>
					<th>Zero-Point Assignments</th>
					<th colspan="2" data-defaultsort="disabled">Course Links</th>
				</tr>
			</thead>
			<tbody>
				{foreach $statistics as $statistic}
					<tr>
						<td>{implode("<br />", array_unique(unserialize($statistic['teacher[sortable_name]s'])))}</td>
						<td><a target="_blank" href="{$smarty.session.canvasInstanceUrl}/courses/{$statistic['course[id]']}">{implode("<br />", explode(": ",$statistic['course[name]']))}</a></td>
						<td>{$statistic['student_count']}</td>
						<td><span{if $statistic['graded_assignment_count'] > 0}{getLevel('average_grading_turn_around', $statistic['average_grading_turn_around'])}{else} class="warning level-3"{/if}>{if $statistic['graded_assignment_count'] > 0}{round($statistic['average_grading_turn_around'], 1)} days{else}No grades{/if}</span></td>
						<td><span{getLevel('average_assignment_lead_time', round($statistic['average_assignment_lead_time']))}>{round($statistic['average_assignment_lead_time'], 1)} days</span></td>
						<td><span{getLevel('average_submissions_graded', $statistic['average_submissions_graded'])}>{round($statistic['average_submissions_graded']*100)}%</span></td>
						<td>{$statistic['assignments_due_count']}</td>
						<td><span{getLevel('dateless_assignment_count', round($statistic['dateless_assignment_count'], 1))}>{$statistic['dateless_assignment_count']}</span></td>
						<td><span{getLevel('created_after_due_count', $statistic['created_after_due_count'])}>{$statistic['created_after_due_count']}</span></td>
						<td><span{getLevel('gradeable_assignment_count', $statistic['gradeable_assignment_count'])}>{$statistic['gradeable_assignment_count']}</span></td>
						<td><span{getLevel('graded_assignment_count', $statistic['graded_assignment_count'])}>{$statistic['graded_assignment_count']}</span></td>
						<td>{if strlen($statistic['oldest_ungraded_assignment_name']) > 0}<a href="{$statistic['oldest_ungraded_assignment_url']}">{$statistic['oldest_ungraded_assignment_name']}</a><br /><small>(due {$statistic['oldest_ungraded_assignment_due_date']|date_format:'F j, Y'})</small>{else}-{/if}</td>
						<td><span{getLevel('zero_point_assignment_count', $statistic['zero_point_assignment_count'])}>{$statistic['zero_point_assignment_count']}</span></td>
						<td><a target="_blank" href="{$statistic['gradebook_url']}">Gradebook</a></td>
						<td><a target="_blank" href="{$statistic['analytics_page']}">Grading Analytics</a></td>
					</tr>
				{/foreach}
			</body>
		</table>
	</div>

{/block}