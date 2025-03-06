<div class="flex justify-end gap-2">
    <button type="button" 
            @click.prevent="editRecord('{{ class_basename($record) }}', {{ $record->id }})"
            class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
        Edit
    </button>
    
    <span class="text-gray-300">|</span>
    
    <button type="button"
            @click.prevent="viewRecord('{{ class_basename($record) }}', {{ $record->id }})"
            class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
        View
    </button>
    
    <span class="text-gray-300">|</span>
    
    <button type="button"
            @click.prevent="deleteRecord('{{ class_basename($record) }}', {{ $record->id }})"
            class="text-red-600 hover:text-red-900 font-medium text-sm">
        Delete
    </button>
</div> 