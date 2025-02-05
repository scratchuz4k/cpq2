<template>
  <button
    id="btn-shareable-open"
    class="btn btn-outline-primary me-2"
    @click="openShare()"
  >
    <font-awesome-icon :icon="['fas', 'share-nodes']"></font-awesome-icon>
    Share
  </button>
  <Dialog
    v-model:visible="showShareDialog"
    modal
    :style="{ width: '40rem' }"
    :draggable="false"
  >
    <template #header>
      <h2>Share quote</h2>
    </template>
    <div v-if="!shareLink.link">
      <div class="mb-3">
        <label for="expiration_data" class="form-label">Expiration Date</label>
        <DatePicker
          v-model="shareLink.expires_at"
          dateFormat="dd-mm-yy"
          showIcon
          fluid
          iconDisplay="input"
          :minDate="dateNow.toJSDate()"
        />
      </div>
      <div class="mb-3">
        <label for="expiration_data" class="form-label">Password</label>
        <input
          type="password"
          id="input-shareable-password"
          class="form-control"
          v-model="shareLink.password"
          autocomplete="off"
        />
      </div>

      <div class="mb-3">
        <button
          id="btn-shareable-create"
          class="btn btn-primary"
          @click="createSharedLink()"
        >
          Create
        </button>
        <button class="btn" @click="() => (showShareDialog = !showShareDialog)">
          Cancel
        </button>
      </div>
    </div>
    <div v-else>
      <div class="mb-3">
        <label for="expiration_data" class="form-label">
          Anyone with this link can view the quote.
        </label>
        <div class="input-group mb-3">
          <input
            id="input-shareable-link"
            type="text"
            class="form-control"
            v-model="shareLink.link"
            disabled
          />

          <button class="input-group-text" @click="copyToClipboard(shareLink.link)">
            <font-awesome-icon role="button" :icon="['far', 'clipboard']" />
          </button>
        </div>
        <p class="small">
          Expires at: {{ shareLink.expires_at ?? "without expiration date" }}
        </p>
      </div>
      <div class="mb-3">
        <button class="btn btn-outline-primary" @click="deleteShareLink()">Delete</button>
      </div>
    </div>
  </Dialog>
</template>
<script setup>
import Dialog from "primevue/dialog";
import DatePicker from "primevue/datepicker";
import { DateTime } from "luxon";
import { reactive, ref } from "vue";
import { quote } from "@composables/useQuote";

const showShareDialog = ref(false);
const dateNow = DateTime.now();

const shareLink = reactive({
  password: null,
  expires_at: quote.shared_link?.expires_at ?? null,
  link: quote.shared_link?.url,
  id: quote.shared_link?.id,
});

const openShare = () => {
  showShareDialog.value = true;
};

const createSharedLink = async () => {
  try {
    let res = await axios.post(`/api/shared-links`, {
      shareable_type: "quotes",
      shareable_id: quote.id,
      expires_at: shareLink.expires_at,
      password: shareLink.password,
    });
    shareLink.expires_at = res.data.data.expires_at;
    shareLink.link = res.data.data.url;
    shareLink.id = res.data.data.id;
  } catch (ex) {
    notyf.error(ex.response.data.message);
  }
};

const copyToClipboard = async (msg) => {
  try {
    await navigator.clipboard.writeText(msg);
    notyf.success("Copied to clipboard!");
  } catch (err) {
    notyf.error("Failed to copy text: " + err);
  }
};

const deleteShareLink = async () => {
  try {
    let res = await axios.delete(`/api/shared-links/${shareLink.id}`);
    shareLink.link = null;
    shareLink.expires_at = null;
  } catch (ex) {
    notyf.error(ex.response.data.message);
  }
};
</script>
