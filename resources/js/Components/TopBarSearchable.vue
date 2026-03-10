<template>
    <form class="d-md-flex d-none" @submit.prevent="showSearch()">
        <div class="input-group">
            <input type="search" class="form-control" placeholder="Type to search" v-model="search" />
            <button class="btn btn-primary btn-search" type="submit" id="button-addon2">
                <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
            </button>
        </div>
        <Dialog
            v-model:visible="visible"
            modal
            :style="{ width: '50rem', marginTop: '4rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            :draggable="false"
            position="top"
        >
            <template #header>
                <div class="d-flex align-items-center w-100 me-3">
                    <div class="search-control d-flex align-items-center w-100">
                        <font-awesome-icon :icon="['fas', 'search']"></font-awesome-icon>
                        <input
                            v-model="search"
                            class="search-button mx-3 border-0 hover-0 w-100"
                            placeholder="Search CloudMarket"
                        />
                    </div>
                </div>
            </template>
            <Loader class="py-2" v-if="isLoading"></Loader>
            <div v-if="!isLoading" class="search-results d-flex flex-column pe-3">
                <div class="mb-4" :title="'Accounts'" v-if="results.accounts.length">
                    <p class="h5">Accounts</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="account in results.accounts"
                        :href="route('accounts.edit', { account: account.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon
                                class="opacity-25 me-3"
                                :icon="['far', 'address-card']"
                            ></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ account.id }} - {{ account.name }} </b> / {{ account.reference }}
                                    <br />
                                    <small>{{ format.date(account.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Categories'" v-if="results.categories.length">
                    <p class="h5">Categories</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="category in results.categories"
                        :href="route('categories.edit', { category: category.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon class="opacity-25 me-3" :icon="['far', 'folder']"></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ category.id }} - {{ category.name }} </b>
                                    <br />
                                    <small>{{ format.date(category.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Solution Types'" v-if="results.solution_types.length">
                    <p class="h5">Solution Types</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="solution_type in results.solution_types"
                        :href="route('solution-types.edit', { solution_type: solution_type.solution_type_id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon
                                class="opacity-25 me-3"
                                :icon="['fas', 'circle-nodes']"
                            ></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ solution_type.id }} - {{ solution_type.name }} </b>
                                    <br />
                                    <small>{{ format.date(solution_type.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Steps'" v-if="results.steps.length">
                    <p class="h5">Steps</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="step in results.steps"
                        :href="route('steps.edit', { step: step.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon class="opacity-25 me-3" :icon="['fa', 'stairs']"></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ step.id }} - {{ step.name }} </b>
                                    <br />
                                    <small>{{ format.date(step.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Quotes'" v-if="results.quotes.length">
                    <p class="h5">Quotes</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="quote in results.quotes"
                        :href="route('quotes.show', { quote: quote.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon class="opacity-25 me-3" :icon="['far', 'file']"></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ quote.id }} - {{ quote.account?.name }} </b> / {{ quote.solution_type.name }}
                                    <br />
                                    <small> {{ quote.user.name }} - {{ format.date(quote.updated_at) }}</small>
                                </p>
                                <p class="m-0">
                                    <span class="badge text-bg-secondary">{{ quote.status }}</span>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Suppliers'" v-if="results.suppliers.length">
                    <p class="h5">Suppliers</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="supplier in results.suppliers"
                        :href="route('suppliers.edit', { supplier: supplier.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon class="opacity-25 me-3" :icon="['fas', 'industry']"></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ supplier.id }} - {{ supplier.name }} </b>
                                    <br />
                                    <small>{{ format.date(supplier.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Apis'" v-if="results.apis.length">
                    <p class="h5">APIs</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="api in results.apis"
                        :href="route('apis.edit', { api: api.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon class="opacity-25 me-3" :icon="['fas', 'cloud']"></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ api.id }} - {{ api.name }} </b>
                                    <br />
                                    <small>{{ format.date(api.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Users'" v-if="results.users.length">
                    <p class="h5">Users</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="user in results.users"
                        :href="route('users.edit', { user: user.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon class="opacity-25 me-3" :icon="['fas', 'user']"></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ user.id }} - {{ user.name }} </b>
                                    <br />
                                    <small>{{ format.date(user.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Roles'" v-if="results.roles.length">
                    <p class="h5">Roles</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="role in results.roles"
                        :href="route('roles.edit', { role: role.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon class="opacity-25 me-3" :icon="['fas', 'id-badge']"></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ role.id }} - {{ role.name }} </b>
                                    <br />
                                    <small>{{ format.date(role.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="mb-4" :title="'Email Template'" v-if="results.emailTemplates.length">
                    <p class="h5">Email Templates</p>
                    <a
                        class="card bg-light w-100 text-decoration-none mb-2"
                        v-for="emailTemplate in results.emailTemplates"
                        :href="route('email-templates.edit', { email_template: emailTemplate.id })"
                        title=""
                    >
                        <span class="card-body p-3 d-flex align-items-center">
                            <font-awesome-icon class="opacity-25 me-3" :icon="['fas', 'envelope']"></font-awesome-icon>
                            <span class="w-100 m-0 d-flex justify-content-between">
                                <p class="m-0">
                                    <b>#{{ emailTemplate.id }} - {{ emailTemplate.description }} </b>
                                    <br />
                                    <small>{{ format.date(emailTemplate.updated_at) }}</small>
                                </p>
                            </span>
                        </span>
                    </a>
                </div>
                <div :title="'General'" v-if="!hasAnyResults">
                    <p class="m-auto py-2 text-center">
                        <b>No results found!</b>
                    </p>
                </div>
            </div>
        </Dialog>
    </form>
</template>

<script setup>
import _ from 'lodash';
import Dialog from 'primevue/dialog';
import axios from 'axios';
import { reactive, ref, watch, computed } from 'vue';
import queryString from 'qs';
import { can, hasRole } from '@composables/useAuth';

const visible = ref(false);
const showSearch = () => {
    if (!search.value) {
        return;
    }
    isLoading.value = true;
    visible.value = true;
    searchCloudMarket(search.value, results, isLoading);
};

const hasAnyResults = computed(() => {
    return Object.values(results).some((items) => Array.isArray(items) && items.length > 0);
});

const search = ref('');
const isLoading = ref(false);
const results = reactive({
    accounts: [],
    categories: [],
    solution_types: [],
    steps: [],
    quotes: [],
    suppliers: [],
    apis: [],
    users: [],
    roles: [],
    emailTemplates: []
});

const searchConfigs = [
    {
        key: 'accounts',
        condition: () => hasRole('Super Admin') || can('viewAny account'),
        endpoint: '/api/accounts',
        params: (searchValue) => ({
            columns: 'id,name,reference,contacts.primary_contact_name,contacts.email,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'categories',
        condition: () => hasRole('Super Admin') || can('viewAny category'),
        endpoint: '/api/categories',
        params: (searchValue) => ({
            columns: 'id,name,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'solution_types',
        condition: () => hasRole('Super Admin') || can('viewAny solution_type'),
        endpoint: '/api/solution-types',
        params: (searchValue) => ({
            columns: 'id,solutionType.name,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'steps',
        condition: () => hasRole('Super Admin') || can('viewAny step'),
        endpoint: '/api/steps',
        params: (searchValue) => ({
            columns: 'id,name,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'quotes',
        condition: () => hasRole('Super Admin') || can('viewAny quote'),
        endpoint: '/api/quotes',
        params: (searchValue) => ({
            columns:
                'id,address_data,items.data,items.name,account.name,account.contacts.email,solution_type.name,user.name,status,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'suppliers',
        condition: () => hasRole('Super Admin') || can('viewAny supplier'),
        endpoint: '/api/suppliers',
        params: (searchValue) => ({
            columns: 'id,name,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'apis',
        condition: () => hasRole('Super Admin') || can('viewAny api'),
        endpoint: '/api/apis',
        params: (searchValue) => ({
            columns: 'id,code,name,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'users',
        condition: () => hasRole('Super Admin') || can('viewAny user'),
        endpoint: '/api/users',
        params: (searchValue) => ({
            columns: 'id,email,name,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'roles',
        condition: () => hasRole('Super Admin') || can('viewAny role'),
        endpoint: '/api/roles',
        params: (searchValue) => ({
            columns: 'id,name,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    },
    {
        key: 'emailTemplates',
        condition: () => hasRole('Super Admin') || can('viewAny email_template'),
        endpoint: '/api/email-templates',
        params: (searchValue) => ({
            columns: 'id,description,updated_at',
            search: searchValue,
            limit: 5,
            order: 'desc',
            orderBy: 'updated_at'
        })
    }
];

const useDebouncedSearch = (configs, debounceDelay = 500) => {
    const performSearch = _.debounce(async (searchValue, results, isLoading) => {
        if (!searchValue) {
            return;
        }

        isLoading.value = true;

        try {
            // Filter valid configs and create promise array
            const validPromises = configs
                .filter((config) => config.condition())
                .map((config) => {
                    const query = queryString.stringify(config.params(searchValue));
                    return axios.get(`${config.endpoint}?${query}`).then((res) => ({
                        key: config.key,
                        data: res.data.data
                    }));
                });

            // Execute all requests in parallel with Promise.all
            const responses = await Promise.all(validPromises);

            // Update results
            responses.forEach(({ key, data }) => {
                results[key] = data;
            });
        } catch (error) {
            console.error('Search failed:', error);
            // Optionally reset results or show error state
        } finally {
            isLoading.value = false;
        }
    }, debounceDelay);

    return performSearch;
};

// Usage in your component
const searchCloudMarket = useDebouncedSearch(searchConfigs, 500);

watch(search, () => {
    if (!visible.value || !search.value) {
        return;
    }
    searchCloudMarket(search.value, results, isLoading);
});
</script>
<style scoped>
.search-results {
    max-height: 60vh;
    overflow-y: auto;
}
.card:hover {
    background-color: var(--color-purple-light) !important;
    border-color: var(--color-purple) !important;
    box-shadow:
        inset 0 0 1px #fff,
        inset 0 1px 7px #ebebeb,
        0 3px 6px -3px #bbb;
}
</style>
