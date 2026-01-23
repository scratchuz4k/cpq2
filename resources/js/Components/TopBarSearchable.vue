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
                <div class="d-flex align-items-center w-100">
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
            <Loader v-if="isLoading"></Loader>
            <div v-if="!isLoading" class="search-results d-flex flex-column">
                <div :title="'Accounts'" v-if="results.accounts.length">
                    <p class="search-title">Accounts</p>
                    <a
                        class="search-option"
                        v-for="account in results.accounts"
                        :href="route('accounts.edit', { account: account.id })"
                    >
                        <font-awesome-icon
                            class="search-option-icon me-3"
                            :icon="['far', 'address-card']"
                        ></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ account.id }} - {{ account.name }} </b> / {{ account.reference }}
                                <br />
                                {{ format.date(account.updated_at) }}
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Categories'" v-if="results.categories.length">
                    <p class="search-title">Categories</p>
                    <a
                        class="search-option"
                        v-for="category in results.categories"
                        :href="route('categories.edit', { category: category.id })"
                    >
                        <font-awesome-icon
                            class="search-option-icon me-3"
                            :icon="['far', 'folder']"
                        ></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ category.id }} - {{ category.name }} </b>
                                <br />
                                {{ format.date(category.updated_at) }}
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Solution Types'" v-if="results.solution_types.length">
                    <p class="search-title">Solution Types</p>
                    <a
                        class="search-option"
                        v-for="solution_type in results.solution_types"
                        :href="route('solution-types.edit', { solution_type: solution_type.id })"
                    >
                        <font-awesome-icon
                            class="search-option-icon me-3"
                            :icon="['fas', 'circle-nodes']"
                        ></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ solution_type.id }} - {{ solution_type.name }} </b>
                                <br />
                                {{ format.date(solution_type.updated_at) }}
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Steps'" v-if="results.steps.length">
                    <p class="search-title">Steps</p>
                    <a
                        class="search-option"
                        v-for="step in results.steps"
                        :href="route('steps.edit', { step: step.id })"
                    >
                        <font-awesome-icon class="search-option-icon me-3" :icon="['fa', 'stairs']"></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ step.id }} - {{ step.name }} </b>
                                <br />
                                <span style="font-size: 12px"> {{ format.date(step.updated_at) }}</span>
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Quotes'" v-if="results.quotes.length">
                    <p class="search-title">Quotes</p>
                    <a
                        class="search-option"
                        v-for="quote in results.quotes"
                        :href="route('quotes.show', { quote: quote.id })"
                    >
                        <font-awesome-icon class="search-option-icon me-3" :icon="['far', 'file']"></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ quote.id }} - {{ quote.account?.name }} </b> / {{ quote.solution_type.name }}
                                <br />
                                <span style="font-size: 12px">
                                    {{ quote.user.name }} - {{ format.date(quote.updated_at) }}</span
                                >
                            </p>
                            <p class="m-0">
                                <span class="badge text-bg-secondary">{{ quote.status }}</span>
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Suppliers'" v-if="results.suppliers.length">
                    <p class="search-title">Suppliers</p>
                    <a
                        class="search-option"
                        v-for="supplier in results.suppliers"
                        :href="route('suppliers.edit', { supplier: supplier.id })"
                    >
                        <font-awesome-icon
                            class="search-option-icon me-3"
                            :icon="['fas', 'industry']"
                        ></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ supplier.id }} - {{ supplier.name }} </b>
                                <br />
                                <span style="font-size: 12px"> {{ format.date(supplier.updated_at) }}</span>
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Apis'" v-if="results.apis.length">
                    <p class="search-title">Apis</p>
                    <a class="search-option" v-for="api in results.apis" :href="route('apis.edit', { api: api.id })">
                        <font-awesome-icon class="search-option-icon me-3" :icon="['fas', 'cloud']"></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ api.id }} - {{ api.name }} </b>
                                <br />
                                <span style="font-size: 12px"> {{ format.date(api.updated_at) }}</span>
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Users'" v-if="results.users.length">
                    <p class="search-title">Users</p>
                    <a
                        class="search-option"
                        v-for="user in results.users"
                        :href="route('users.edit', { user: user.id })"
                    >
                        <font-awesome-icon class="search-option-icon me-3" :icon="['fas', 'user']"></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ user.id }} - {{ user.name }} </b>
                                <br />
                                <span style="font-size: 12px"> {{ format.date(user.updated_at) }}</span>
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Roles'" v-if="results.roles.length">
                    <p class="search-title">Roles</p>
                    <a
                        class="search-option"
                        v-for="role in results.roles"
                        :href="route('roles.edit', { role: role.id })"
                    >
                        <font-awesome-icon
                            class="search-option-icon me-3"
                            :icon="['fas', 'id-badge']"
                        ></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ role.id }} - {{ role.name }} </b>
                                <br />
                                <span style="font-size: 12px"> {{ format.date(role.updated_at) }}</span>
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'Email Template'" v-if="results.emailTemplates.length">
                    <p class="search-title">Email Templates</p>
                    <a
                        class="search-option"
                        v-for="emailTemplate in results.emailTemplates"
                        :href="route('email-templates.edit', { email_template: emailTemplate.id })"
                    >
                        <font-awesome-icon
                            class="search-option-icon me-3"
                            :icon="['fas', 'envelope']"
                        ></font-awesome-icon>
                        <div class="w-100 m-0 d-flex justify-content-between">
                            <p class="m-0">
                                <b>#{{ emailTemplate.id }} - {{ emailTemplate.description }} </b>
                                <br />
                                <span style="font-size: 12px"> {{ format.date(emailTemplate.updated_at) }}</span>
                            </p>
                        </div>
                    </a>
                </div>
                <div :title="'General'" v-if="!hasAnyResults">
                    <p class="m-0">
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
import queryString from 'query-string';
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
</style>
