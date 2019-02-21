@section('scripts')
@parent

<link href="{{ asset('vendor/laravel-uploads/css/laravel-uploads.css') }}" rel="stylesheet">

@endsection

@php
	if (!isset($label)) {
		switch ($type) {
			case 1:
				$label = 'Imagen';
				break;
			case 2:
				$label = 'Imágenes';
				break;
			case 3:
				$label = 'Archivo';
				break;
			case 4:
				$label = 'Archivos';
				break;
		}
	}
@endphp

<div class="form-group">
	<label>{{ $label }}</label>
	<table class="images-container minigallery_{{ $number }}">
		<thead>
			<tr>
				<th>Vista previa</th>
				<th>Título</th>
				<th>Acciones</th>
			</tr>
		</thead>
		<tbody id="sortable_{{ $number }}">
		@if (isset($images) && sizeof($images))
			@foreach ($images as $image)
				<tr class="image" id="{{ $image->id }}" data-url="{{ $image->url }}" data-id="{{ $image->id }}" data-folder="{{ $folder }}">
					<td><img src="{{ route('image', [$folder, $image->url]) }}" alt="Imagen" /></td>
					<td><input type="text" name="{{ $field }}_titles[]" placeholder="Título" value="{{ $image->title }}"></td>
					<td>
						<a class="crop btn btn-sm btn-primary" href="{{ route('crop', [$folder, $image->url, $cropType]) }}"><i class="fa fa-crop"></i> Recortar</a>
						<a class="delete btn btn-sm btn-danger" href="{{ route('deleteImageStorage', [$folder, $image->url]) }}"><i class="fa fa-trash"></i> Eliminar</a>
					</td>
				</tr>
			@endforeach
		@elseif (isset($image))
			<tr class="image" id="{{ $image->id }}" data-url="{{ $image->url }}" data-id="{{ $image->id }}" data-folder="{{ $folder }}">
				<td><img src="{{ route('image', [$folder, $image->url]) }}" alt="Imagen" /></td>
				<td><input type="text" name="{{ $field }}_title" placeholder="Título" value="{{ $image->title }}"></td>
				<td>
					<a class="crop btn btn-sm btn-primary" href="{{ route('crop', [$folder, $image->url, $cropType]) }}"><i class="fa fa-crop"></i> Recortar</a>
					<a class="delete btn btn-sm btn-danger" href="{{ route('deleteImageStorage', [$folder, $image->url]) }}"><i class="fa fa-trash"></i> Eliminar</a>
				</td>
			</tr>
		@endif
		@if (isset($files) && sizeof($files))
			@foreach ($files as $file)
				<tr class="image" id="{{ $file->id }}" data-url="{{ $file->url }}" data-id="{{ $file->id }}" data-folder="{{ $folder }}">
					<td>{{ $file->url }}</td>
					<td><input type="text" name="{{ $field }}_titles[]" placeholder="Título" value="{{ $file->title }}"></td>
					<td>
						<a class="crop btn btn-sm btn-primary" href="{{ route('download', [$folder, $file->url]) }}"><i class="fa fa-download"></i> Descargar</a>
						<a class="delete btn btn-sm btn-danger" href="{{ route('deleteFileModel', $file->id) }}"><i class="fa fa-trash"></i> Eliminar</a>
					</td>
				</tr>
			@endforeach
		@elseif (isset($file))
			<tr class="image" id="{{ $file->id }}" data-url="{{ $file->url }}" data-id="{{ $file->id }}" data-folder="{{ $folder }}">
				<td>{{ $file->url }}</td>
				<td><input type="text" name="{{ $field }}_title" placeholder="Título" value="{{ $file->title }}"></td>
				<td>
					<a class="crop btn btn-sm btn-primary" href="{{ route('download', [$folder, $file->url]) }}"><i class="fa fa-download"></i> Descargar</a>
					<a class="delete btn btn-sm btn-danger" href="{{ route('deleteFileModel', $file->id) }}"><i class="fa fa-trash"></i> Eliminar</a>
				</td>
			</tr>
		@endif
		</tbody>
	</table>
	<a class="uploadFile_{{ $number }} upload_button btn btn-sm btn-primary" href="javascript:;"><i class="fa fa-upload"></i> Subir archivo(s)</a>
</div>

@section('scripts')
@parent

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="{{ asset('vendor/laravel-uploads/js/laravel-uploads.js') }}"></script>

<script>
	$(function() {
		switch ({{ $type }}) {
			case 1: // Una única imagen
				$('.minigallery_{{ $number }}').dropzone({
					url: '/upload',
					clickable: '.uploadFile_{{ $number }}',
					maxFilesize: {{ config('upload.max_image_size') }}, // MB
					acceptedFiles: '{{ config('upload.accepted_image_extensions') }}',
					init: function() {
						this.on("sending", function(file, xhr, formData) {
							formData.append("_token", "{{ csrf_token() }}");
							formData.append("folder", "{{ $folder }}");
							formData.append("cropType", "{{ isset($cropType) ? $cropType : null }}");
						});
					},
					complete: function(file) {
						this.removeFile(file);
					},
					success: function(file, response) {
                        $fileDiv = '<tr class="image" data-url="' + response.filename + '" data-folder="' + response.folder + '">';
                        $fileDiv += '<td><img src="/image/' + response.folder + '/' + response.filename + '" alt="Imagen" /></td>';
                        $fileDiv += '<td><input type="text" name="{{ $field }}_title" placeholder="Título"></td>';
                        $fileDiv += '<td><a class="crop btn btn-sm btn-primary" href="/crop/' + response.folder + '/' + response.filename + '/' + response.cropType + '"><i class="fa fa-crop"></i> Recortar</a><a class="delete btn btn-sm btn-danger" href="/crop/' + response.folder + '/' + response.filename + '"><i class="fa fa-trash"></i> Eliminar</a></td>';
                        $fileDiv += '</tr>';

						$('#{{ $formid }}').append($('<input type="hidden" name="{{ $field }}" value="' + response.filename + '" />'));
						$('#{{ $formid }} .minigallery_{{ $number }} .image').remove();
						$('#{{ $formid }} .minigallery_{{ $number }}').append($fileDiv);
						$('.upload_{{ $number }}').hide('fade');
					}
				});
				break;
			case 2: // Múltiples imágenes
				$('.minigallery_{{ $number }}').dropzone({
					url: '/upload',
					clickable: '.uploadFile_{{ $number }}',
					maxFilesize: {{ config('upload.max_image_size') }}, // MB
					acceptedFiles: '{{ config('upload.accepted_image_extensions') }}',
					init: function() {
						this.on("sending", function(file, xhr, formData) {
							formData.append("_token", "{{ csrf_token() }}");
							formData.append("folder", "{{ $folder }}");
							formData.append("cropType", "{{ isset($cropType) ? $cropType : null }}");
						});
					},
					complete: function(file) {
						this.removeFile(file);
					},
					success: function(file, response) {
                        $fileDiv = '<tr class="image" data-url="' + response.filename + '" data-folder="' + response.folder + '">';
                        $fileDiv += '<td><img src="/image/' + response.folder + '/' + response.filename + '" alt="Imagen" /></td>';
                        $fileDiv += '<td><input type="text" name="{{ $field }}_titles[]" placeholder="Título"></td>';
                        $fileDiv += '<td><a class="crop btn btn-sm btn-primary" href="/crop/' + response.folder + '/' + response.filename + '/' + response.cropType + '"><i class="fa fa-crop"></i> Recortar</a><a class="delete btn btn-sm btn-danger" href="/crop/' + response.folder + '/' + response.filename + '"><i class="fa fa-trash"></i> Eliminar</a></td>';
                        $fileDiv += '</tr>';

						$('#{{ $formid }}').append($('<input type="hidden" name="{{ $field }}[]" value="' + response.filename + '" />'));
						$('#{{ $formid }} .minigallery_{{ $number }}').append($fileDiv);
						$('.upload_{{ $number }}').hide('fade');
					}
				});
				break;
			case 3: // Un único archivo
				$('.minigallery_{{ $number }}').dropzone({
					url: '/upload',
					clickable: '.uploadFile_{{ $number }}',
					maxFilesize: {{ config('upload.max_file_size') }}, // MB
					acceptedFiles: '{{ config('upload.accepted_file_extensions') }}',
					init: function() {
						this.on("sending", function(file, xhr, formData) {
							formData.append("_token", "{{ csrf_token() }}");
							formData.append("folder", "{{ $folder }}");
							formData.append("cropType", "{{ isset($cropType) ? $cropType : null }}");
						});
					},
					complete: function(file) {
						this.removeFile(file);
					},
					success: function(file, response) {
                        $fileDiv = '<tr class="image" data-url="' + response.filename + '" data-folder="' + response.folder + '">';
                        $fileDiv += '<td>' + response.filename + '</td>';
                        $fileDiv += '<td><input type="text" name="{{ $field }}_title" placeholder="Título"></td>';
                        $fileDiv += '<td><a class="download btn btn-sm btn-primary" href="/descargar/' + response.folder + '/' + response.filename + '"><i class="fa fa-download"></i> Descargar</a><a class="delete btn btn-sm btn-danger" href="/eliminarFichero/' + response.folder + '/' + response.filename + '"><i class="fa fa-trash"></i> Eliminar</a></td>';
                        $fileDiv += '</tr>';

						$('#{{ $formid }}').append($('<input type="hidden" name="{{ $field }}" value="' + response.filename + '" />'));
						$('#{{ $formid }} .minigallery_{{ $number }} .image').remove();
						$('#{{ $formid }} .minigallery_{{ $number }}').append($fileDiv);
						$('.upload_{{ $number }}').hide('fade');
					}
				});
				break;
			case 4: // Múltiples archivos
				$('.minigallery_{{ $number }}').dropzone({
					url: '/upload',
					clickable: '.uploadFile_{{ $number }}',
					maxFilesize: {{ config('upload.max_file_size') }}, // MB
					acceptedFiles: '{{ config('upload.accepted_file_extensions') }}',
					init: function() {
						this.on("sending", function(file, xhr, formData) {
							formData.append("_token", "{{ csrf_token() }}");
							formData.append("folder", "{{ $folder }}");
							formData.append("cropType", "{{ isset($cropType) ? $cropType : null }}");
						});
					},
					complete: function(file) {
						this.removeFile(file);
					},
					success: function(file, response) {
                        $fileDiv = '<tr class="image" data-url="' + response.filename + '" data-folder="' + response.folder + '">';
                        $fileDiv += '<td>' + response.filename + '</td>';
                        $fileDiv += '<td><input type="text" name="{{ $field }}_titles[]" placeholder="Título"></td>';
                        $fileDiv += '<td><a class="download btn btn-sm btn-primary" href="/descargar/' + response.folder + '/' + response.filename + '"><i class="fa fa-download"></i> Descargar</a><a class="delete btn btn-sm btn-danger" href="/eliminarFichero/' + response.folder + '/' + response.filename + '"><i class="fa fa-trash"></i> Eliminar</a></td>';
                        $fileDiv += '</tr>';

						$('#{{ $formid }}').append($('<input type="hidden" name="{{ $field }}[]" value="' + response.filename + '" />'));
						$('#{{ $formid }} .minigallery_{{ $number }}').append($fileDiv);
						$('.upload_{{ $number }}').hide('fade');
					}
				});
				break;
		}

		var clicked;

		// Cuando se le da al botón de eliminar un archivo...
		$(document).on('click', '.images-container .image .delete', function(e) {
			e.preventDefault();
			clicked = $(this).parent().parent();
			// Si ya está persistido, eliminamos el archivo de la base de datos y de la vista.
			if (clicked.attr('data-id')) {
				$.get($(this).attr('href'), {}, function() {
					clicked.remove();
				});
			} else {
				// Si no está persistido, eliminamos el input y el archivo de la vista.
				$('input[value="' + clicked.attr('data-url') + '"]').remove();
				clicked.remove();
			}
		});

		$("#sortable_{{ $number }}").sortable({
			placeholder: "ui-state-highlight",
			items: "tr",
			update: function(event, ui) {
				var order = $(this).sortable('toArray');
				var positions = order.join(';');
				$.get('{{ in_array($type, [1, 2]) ? route('images.sort') : route('files.sort') }}', {positions: positions});
			}
		});
		$("#sortable").disableSelection();

	});
</script>

@endsection
