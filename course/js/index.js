/*jslint browser */
/*global $,fetch,Chart */
var gradingAnalytics = {
  loadChart: function(domId, chartUrl, course_id, department_id = false) {
    fetch("chart/" + chartUrl + "?course_id=" + course_id + (department_id !== false ? "&department_id=" + department_id : ""))
      .then(function (response) {
          return response.json();
      }).then(function (data) {
          new Chart($(domId), data);
      });
  }
}
