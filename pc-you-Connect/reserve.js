(function () {
  var facility = document.getElementById('facility_id');
  var dateInput = document.getElementById('date');
  var startInput = document.getElementById('time_start');
  var endInput = document.getElementById('time_end');
  var monthLabel = document.getElementById('calendarMonth');
  var days = document.getElementById('calendarDays');
  var today = new Date();
  today.setHours(0, 0, 0, 0);
  var viewDate = new Date(today.getFullYear(), today.getMonth(), 1);
  var schedules = window.reservationSchedules || [];

  function pad(value) { return String(value).padStart(2, '0'); }
  function dateKey(year, month, day) { return year + '-' + pad(month + 1) + '-' + pad(day); }
  function minutes(value) {
    var parts = value.split(':');
    return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
  }
  function isAvailable(key) {
    var selectedFacility = facility.value;
    var start = startInput.value;
    var end = endInput.value;
    if (!selectedFacility || !start || !end || minutes(end) <= minutes(start)) return true;
    return !schedules.some(function (booking) {
      return String(booking.facility_id) === selectedFacility && booking.date === key &&
        minutes(booking.time_start) < minutes(end) && minutes(booking.time_end) > minutes(start);
    });
  }
  function chooseDate(key) {
    dateInput.value = key;
    render();
  }
  function render() {
    var year = viewDate.getFullYear();
    var month = viewDate.getMonth();
    var firstDay = new Date(year, month, 1).getDay();
    var daysInMonth = new Date(year, month + 1, 0).getDate();
    monthLabel.textContent = viewDate.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
    days.innerHTML = '';
    for (var blank = 0; blank < firstDay; blank += 1) {
      days.insertAdjacentHTML('beforeend', '<span class="calendar-blank"></span>');
    }
    for (var day = 1; day <= daysInMonth; day += 1) {
      var key = dateKey(year, month, day);
      var date = new Date(year, month, day);
      var past = date < today;
      var available = !past && isAvailable(key);
      var selected = dateInput.value === key;
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'calendar-day ' + (available ? 'is-available' : 'is-taken') + (selected ? ' is-selected' : '');
      button.textContent = day;
      button.disabled = past;
      button.title = past ? 'Past date' : (available ? 'Available' : 'Not available for this time');
      if (available && !past) button.addEventListener('click', function (event) { chooseDate(event.currentTarget.dataset.date); });
      button.dataset.date = key;
      days.appendChild(button);
    }
  }

  document.getElementById('previousMonth').addEventListener('click', function () {
    var previous = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
    if (previous >= new Date(today.getFullYear(), today.getMonth(), 1)) { viewDate = previous; render(); }
  });
  document.getElementById('nextMonth').addEventListener('click', function () {
    viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
    render();
  });
  [facility, dateInput, startInput, endInput].forEach(function (input) { input.addEventListener('change', render); });
  render();
})();
