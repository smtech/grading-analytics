/*jslint browser */
/*global $,fetch,Chart */
var graphs = {
    turnAroundComparison: function (domId, course_id, department_id = false) {
        fetch("chart/turn-around-comparison.php?course_id=" + course_id + (department_id !== false ? "&department_id=" + department_id : ""))
            .then(function (response) {
                return response.json();
            }).then(function (data) {
                var tac = new Chart($(domId), data);
                console.log(tac);
            });
    }
};
