<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>ABC Store Birthday Reminder</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>
<body>
  <div id="root-container">
    <div class="container">
      <div class="my-3">
        <h1>ABC Store Birthday Reminder</h1>
        <p>To send birthday emails to our users at the moment their birth day starts with consideration to their timezone.</p>
        Our timezone: <select name="client_timezone" id="clientTimezonePicker" onchange="loadBirthdateReminderList()" required></select>
      </div>

      <!-- Button trigger modal -->
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
        Add Birthday
      </button>

      <button type="button" class="btn btn-primary" onclick="loadBirthdateReminderList()">
        Refresh List
      </button>

      <!-- Modal -->
      <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Add Birthdate</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form onsubmit="addBirthday(this, event)" method="post" class="mt-0">
              <div class="modal-body">
                <div class="form-group">
                  <label>Full name</label>
                  <input class="form-control" name="name" placeholder="eg: James Hilton" required />
                </div>

                <div class="form-group mt-2">
                  <label>Birthdate (YYYY-MM-DD)</label>
                  <input class="form-control" name="birthdate" placeholder="eg: 1990-01-01" id="datepicker" pattern="\d\d\d\d-\d\d-\d\d" required />
                </div>

                <div class="form-group mt-2">
                  <label>Timezone</label>
                  <select class="form-control" name="timezone" id="timezonePicker" required>
                    <option value="">Select a timezone</option>
                  </select>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="table-responsive mt-3">
          <table class="table table-striped table-sm">
            <thead>
              <tr>
                <th>Name</th>
                <th>Birthdate</th>
                <th>Message</th>
              </tr>
            </thead>
            <tbody>
              <tr></tr>
            </tbody>
          </table>
        </div>
    </div>
  </div>

  <script
    src="https://code.jquery.com/jquery-3.6.0.min.js"
    integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
    crossorigin="anonymous"></script>

  <script
    src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"
    integrity="sha256-eTyxS0rkjpLEo16uXTS0uVCS4815lc40K2iVpWDvdSY="
    crossorigin="anonymous"></script>

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"
    integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13"
    crossorigin="anonymous"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.10/moment-timezone-with-data.js"></script>

  <script>
    $("#datepicker").datepicker({
      dateFormat: "yy-mm-dd",
      changeMonth: true,
      changeYear: true,
      yearRange: "1900:2022"
    });

    var tz = moment.tz.guess();
    var timezones = moment.tz.names();
    var options = timezones.map(function(row) {return '<option value="' + row + '">' + row + '</option>';});

    $('#timezonePicker').append(options);
    $('#clientTimezonePicker').append(options);
    $('#clientTimezonePicker').val(tz);

    function addBirthday(el, event) {
      event.preventDefault();

      $.ajax({
        type: "POST",
        url: "/birthdays",
        data: $(el).serialize(),
        dataType: "json",
        success: function(data) {
          el.reset();
          $('.modal').modal('hide');
          loadBirthdateReminderList();
        },
        error: function(data) {
          $('.modal-body').append('<div class="mt-3 alert alert-danger" role="alert">'+data.responseText+'</div>');
        }
      });
    }

    function loadBirthdateReminderList() {
      var clientTimezone = $('#clientTimezonePicker').val();

      $.ajax({
        type: "GET",
        url: "/birthdays",
        dataType: "json",
        data: {
          timezone: clientTimezone,
          datetime: moment().format("YYYY-MM-DD HH:mm:ss")
        },
        success: function(data) {
          $('tbody').empty();

          for (row of data) {
            $('tbody').append(`
              <tr class="${row.isBirthday ? 'table-success' : ''}">
                <td>${row.name}</td>
                <td>${row.birthdate}</td>
                <td>${row.message}</td>
              </tr>
            `);
          }
        },
        error: function() {
          alert('error handling here');
        }
      });
    }

    loadBirthdateReminderList();
  </script>
</body>
</html>
