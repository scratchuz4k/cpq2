<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const props = defineProps({
  results: {
    type: Object,
    default: null,
  },
});

const users = ref([]);

const showForm = ref(false);
const isLoading = ref(false);
const selectedItems = ref([]);
let showModal = ref(false);

const onFileChange = (event) => {
  formFile.file = event.target.files[0];
};

const formFile = reactive({
  file: null,
});

const commission = ref(0);

const submitFileForm = () => {
  isLoading.value = true;
  router.post(
    `/users/import`,
    {
      file: formFile.file,
    },
    {
      onSuccess: () => {
        notyf.success(`File imported successfully`);
        isLoading.value = false;
      },
      onError: (errors) => {
        let string = "";
        Object.keys(errors).forEach((key) => {
          string += errors[key] + "<br>";
        });
        notyf.error(string);
        isLoading.value = false;
      },
    }
  );
};

const saveCommission = async (data, commission, toggleModal = null) => {
  try {
    let res = await axios.put(`/api/users/${data.id}`, { commission_rate: commission });
    data.commission_rate = commission;
    if (toggleModal !== null) toggleModal();
  } catch (e) {
    notyf.error(e.message);
    if (toggleModal !== null) toggleModal();
  }
};

const saveCommissionBulk = (items, toggleModal = null) => {
  items.forEach((item) => {
    saveCommission(item, commission.value, toggleModal);
  });
  showModal.value = false;
  commission.value = 0;

  notyf.success("Updated with Success");
};

const items = ref([
  { label: "Home", url: "/" },
  { label: "My Account", url: "/myaccount" },
  { label: "Users" },
]);
</script>
<template>
  <Head title="Users" />
  <AuthenticatedLayout>
    <div class="panel">
      <Header title="Users" :breadcrumbs="items">
        <template #buttons>
          <PrimaryButton
            class="btn btn-secondary me-2"
            @click="showModal = true"
            :disabled="!(selectedItems.length > 0)"
            >Add Commission</PrimaryButton
          >
          <Link
            id="link-to-create-user"
            :href="route('users.create')"
            class="btn btn-primary me-2"
          >
            <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New User
          </Link>
          <div class="btn btn-secondary me-2" @click.prevent="showForm = !showForm">
            <font-awesome-icon :icon="['fas', 'file']"></font-awesome-icon> Import from
            CSV
          </div>
        </template>
      </Header>
      <div
        v-if="showForm"
        id="csv-import-form"
        class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4 pb-4 border-bottom"
      >
        <form @submit.prevent="submitFileForm" enctype="multipart/form-data">
          <h4>Import Users from CSV</h4>
          <p>
            Your CSV must contain the following columns: name*, email*, password, sso,
            managername. * must not be blank
          </p>
          <input type="file" accept=".csv" required @input="onFileChange" />
          <button type="submit" class="btn btn-primary ms-2" :disabled="isLoading">
            <span v-if="isLoading">
              <font-awesome-icon
                :icon="['fa', 'spinner']"
                class="fa-spin"
              ></font-awesome-icon>
              Uploading...
            </span>
            <span v-else>Upload</span>
          </button>
        </form>
      </div>
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <TreeTable
          id="users-table"
          :allow="{
            update: true,
            delete: true,
            link: false,
            commission: true,
            create: true,
          }"
          :columns="[
            { data: 'id', title: '#' },
            { data: 'sso', title: 'SSO', style: 'width:4%' },
            { data: 'name', title: 'Name' },
            { data: 'email', title: 'Email' },
            { data: 'commission_rate', title: 'Commission' },
            { data: 'roles.name', title: 'Role' },
          ]"
          route="/users"
          v-model="selectedItems"
        >
          <template #dialog_content="{ data, toggleModal }">
            <div class="panel">
              <div class="row">
                <label for="user-commission-rate" class="form-label"
                  >Commission Rate:</label
                >
                <div class="input-group">
                  <input
                    id="user-commission-rate"
                    type="number"
                    class="form-control"
                    v-model="commission"
                  />
                  <span class="input-group-text">%</span>
                </div>
                <div class="d-flex justify-content-end mt-3">
                  <div class="btn btn-secondary me-2" @click="toggleModal()">Cancel</div>
                  <PrimaryButton
                    class="btn btn-primary"
                    @click="saveCommissionBulk([data], toggleModal)"
                  >
                    Save
                  </PrimaryButton>
                </div>
              </div>
            </div>
          </template>
          <template #sso="{ value }">
            <font-awesome-icon
              v-if="value === true"
              :icon="['fas', 'circle-check']"
              style="font-size: 1.125rem"
              class="text-primary"
            />
            <font-awesome-icon
              v-else
              :icon="['far', 'circle-xmark']"
              style="font-size: 1.125rem"
              class="text-secondary"
            />
          </template>
          <template #commission_rate="{ value }"> {{ value }}% </template>
        </TreeTable>
      </div>
    </div>
    <Dialog
      v-model:visible="showModal"
      modal
      :style="{ marginTop: '4rem' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      :draggable="false"
      position="top"
    >
      <div class="panel">
        <div class="row">
          <label for="user-bulk-commission-rate" class="form-label"
            >Commission Rate:</label
          >
          <div class="input-group">
            <input
              id="user-bulk-commission-rate"
              type="number"
              class="form-control"
              v-model="commission"
            />
            <span class="input-group-text">%</span>
          </div>
          <div class="d-flex justify-content-end mt-3">
            <div class="btn btn-secondary me-2" @click="showModal = false">Cancel</div>
            <PrimaryButton
              class="btn btn-primary"
              @click="saveCommissionBulk(selectedItems)"
              :disabled="!(selectedItems.length > 0)"
            >
              Save
            </PrimaryButton>
          </div>
        </div>
      </div>
    </Dialog>
  </AuthenticatedLayout>
</template>
