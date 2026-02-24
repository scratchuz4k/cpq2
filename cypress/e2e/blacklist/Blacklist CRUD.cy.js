import { generateHash, getCurrentDate } from '../../support/utils.js';

const product_name = 'Software Defined FTTP';
const provider = 'Giacom';
const subprovider = 'BT';
const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };

describe('Blacklist Crud', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    it('Create a Blacklist Item', () => {
        // create a new item
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-products').click();
        cy.get('#blacklist-index-btn').click();
        cy.get('#blacklist-create-btn').click();
        cy.selectOption('#item-provider', provider);
        cy.selectOption('#item-subprovider', subprovider);
        cy.get('#btn-save-item').click();
        cy.get('#datatable-search').type(provider);
        cy.get('.p-datatable-table').contains(provider);
    });

    it('Check product results for Blacklisted Item', () => {
        // Create an account
        cy.visit('/quotes/create');
        cy.get('#link-to-create-account').click();
        cy.get('#account-name').type(account.name);
        cy.get('#account-reference').type(account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');

        cy.visit('/quotes/create');
        cy.selectLazyOption('account_id', account.name);
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.wait(1000);
        cy.selectOption('#select_address', 1);
        cy.get('.step-action-bar #btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();
        // Scroll back to top
        cy.scrollTo('top');
        //Provider filter
        cy.get('#provider-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains(provider).click();
            });

        //SubProvider filter
        cy.get('#subprovider-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains(subprovider).click();
            });

        cy.get('#no-results').should('exist').contains('No results match your filters.');
    });

    it('Edit a Blacklist Item', () => {
        cy.visit('/blacklists');
        cy.wait(500);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#item-name').clear().type(product_name);

        cy.get('#btn-save-item').click();
        cy.get('#datatable-search').type(product_name);
        cy.get('.p-datatable-table').contains(product_name);
    });

    it('Delete a Blacklist Item', () => {
        cy.visit('/blacklists');
        cy.get('#datatable-search').type(product_name);
        cy.wait(500);
        cy.get('#datatable-btn-0-delete').click();

        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Removed with success');
    });
});
