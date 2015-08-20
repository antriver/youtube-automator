<ul class="description-changes">
@foreach ($descriptionChanges as $change)

    <li
        data-id="{{ $change->id }}"
        data-execute-at="{{ $change->execute_at }}"
        data-execute-mins-after-publish="{{ $change->execute_mins_after_publish }}">

        @if ($change->executed_at)
        <span class="label label-sm label-info"><i class="fa fa-check-circle"></i> Changed at {{ $change->executed_at }}</span>
        @elseif ($change->execute_at)
        <span class="label label-sm label-default"><i class="fa fa-clock-o"></i> Scheduled for {{ $change->execute_at }}</span>
        @elseif ($change->execute_mins_after_publish)
        <span class="label label-sm label-default"><i class="fa fa-clock-o"></i> Scheduled for {{ $change->execute_mins_after_publish }} mins after publishing</span>
        @endif

        <a href="#" class="btn-delete-description-change btn btn-black btn-xs"><i class="fa fa-trash-o"></i> Delete</a>
        <a href="#" class="btn-edit-description-change btn btn-black btn-xs"><i class="fa fa-pencil"></i> Edit</a>

        {{ Lang::wordTruncate($change->description, 200) }}

        <span style="display:none;" class="full-description">{{ $change->description }}</span>
     </li>

@endforeach
</ul>
