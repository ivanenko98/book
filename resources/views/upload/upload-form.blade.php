<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
</head>
<body>
<form method="post" action="{{ route('upload_file') }}" enctype="multipart/form-data">
    <input name="_token" type="hidden" value="{{ csrf_token() }}">

    Name<input type="text" name="name">
    Author<input type="text" name="author">
    Description<input type="text" name="description">
    {{--Likes<input type="text" name="likes">--}}
    {{--Percent<input type="text" name="percent">--}}
    Folder_id<input type="text" name="folder_id">
    User_id<input type="text" name="user_id">
    <input type="file" multiple name="file[]">
    <button type="submit">Загрузить</button>
</form>
</body>
</html>