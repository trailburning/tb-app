<form action="/v1/route/<?php echo $routeid; ?>/pictures/add" method="post" enctype="multipart/form-data">
  <input type="file" name="pictures[]" multiple>
  <input type="submit">
</form>
