<script setup>
defineProps({
    subject: {
        type: String,
        default: 'user'
    }
});
</script>

<template>
    <tooltip-with-icon class="ms-1">
        <div class="small text-start" style="width: 320px">
            <p class="mb-1">
                If your user account <b>is not</b> assigned to any groups, no groups are selected by default for this
                {{ subject }}. You may:
            </p>
            <ul class="mb-2 ps-3">
                <li>Make the {{ subject }} unassigned to any groups</li>
                <li>Assign the {{ subject }} to any group or combination of groups</li>
            </ul>
            <p class="mb-1">
                If your user account <b>is</b> assigned to one or more groups, this {{ subject }} must be assigned to at
                least one of your groups. You may assign:
            </p>
            <ul class="mb-2 ps-3">
                <li>All of your groups</li>
                <li>Some of your groups</li>
                <li>A single group that you belong to</li>
            </ul>
            <p class="mb-0">
                You cannot assign groups that you do not belong to, and you cannot remove all group assignments from the
                {{ subject }}.
            </p>
        </div>
    </tooltip-with-icon>
</template>

<style scoped></style>
