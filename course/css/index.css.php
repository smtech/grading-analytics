<?php

require_once 'common.inc.php';

// TODO grow up and use SASS or something to propocess your CSS!

?>

.average-underline {
    border-bottom: 1px <?= GRAPH_AVERAGE_COLOR ?> <?= GRAPH_AVERAGE_STYLE ?>;
}

.one-week-underline {
    border-bottom: 1px <?= GRAPH_1_WEEK_COLOR ?> <?= GRAPH_1_WEEK_STYLE ?>;
}

.two-week-underline {
    border-bottom: 1px <?= GRAPH_2_WEEK_COLOR ?> <?= GRAPH_2_WEEK_STYLE ?>;
}

.highlight-column:after {
    content: " \258C";
    color: <?= GRAPH_HIGHLIGHT_COLOR ?>;
}

.data-column:after {
    content: " \258C";
    color: <?= GRAPH_DATA_COLOR ?>;
}
