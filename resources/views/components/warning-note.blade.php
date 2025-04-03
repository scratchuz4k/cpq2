<!-- This component also exists as a Vue component, any changes made here should also be made in the Vue component (resources/js/Components/WarningNote.vue) -->
<div class="small">
    <div class="mb-2 fw-bold" style="color:#dc3545">Important Information:</div>
    @foreach ($warnings as $key => $value)
        @if ($value)
            <div class="mb-2" style="color:#dc3545; line-height: 1.2">
                <span class="small fw-bold bg-light rounded-2 py-1 px-2 me-1 text-capitalize">
                    {{ strtoupper($key) }}
                </span>
                <i>
                    Please note, quote references for
                    {{ strtoupper($key) }} quotes cannot currently
                    be used to place an order. To obtain a valid quote for ordering
                    please generate the quote directly on the
                    {{ strtoupper($key) }}
                    portal.
                </i>
            </div>
        @endif
    @endforeach
</div>
