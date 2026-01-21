const { isSymbol } = require('lodash');

const provider = 'Giacom';
const subprovider = 'BT';

describe('Creates a Mobile Quote', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Formats connectivity table to simple view', () => {
        cy.visit('/quotes');
        cy.get('#my-account').click();
        cy.get('#link-solutions').click();
        cy.get('#datatable-search').type('Connectivity');
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();
        cy.get('#2-2').click();
        cy.get('#solution_type-component').click();
        cy.wait(500);
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.get('li[aria-label="Simple"]').should('exist').click();
            });

        cy.get('#btn-save-solution-type').click();
        cy.get('.notyf').contains('Updated with Success');
    });

    it('Creates Connectivity quote and has simple Table', () => {
        cy.visit('/quotes/create');
        cy.selectLazyOption('account_id');
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('#btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('#btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();

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

        // Scroll back to top
        cy.scrollTo('top');
        cy.get('#table-header-setup').click();
        cy.wait(1000);
        cy.get('#table-header-setup').click();
        cy.wait(1000);

        //Add products to quote

        cy.get('tbody tr button').eq(0).click();
        cy.get('#connectivity-product-1').contains('Primary Product');

        //Save quote
        cy.get('.continue-btn').eq(0).click();
        cy.get('.Simple-item').its('length').should('eq', 2);
        cy.get('#table-simple > thead').contains('Quote Reference');
        cy.get('#table-simple > thead').contains('Speed');
        cy.get('#table-simple > thead').contains('Bearer');
        cy.get('#table-simple > thead').contains('Price Plan');
        cy.get('#table-simple > thead').contains('Months');
        cy.get('#table-simple > thead').contains('Price');
        cy.get('#table-simple > thead').contains('Total');
    });
    it('Formats connectivity table to DealMaker view', () => {
        cy.visit('/quotes');
        cy.get('#my-account').click();
        cy.get('#link-solutions').click();
        cy.get('#datatable-search').type('Connectivity');
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();
        cy.get('#2-2').click();
        cy.get('#solution_type-component').click();
        cy.wait(500);
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.contains('li', 'DealMaker').should('exist').click();
            });

        cy.get('#btn-save-solution-type').click();
        cy.get('.notyf').contains('Updated with Success');
    });
    it('Creates Connectivity quote and has DealMaker Table', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.recurring-item').its('length').should('eq', 1);
        cy.get('#table-recurring > thead').contains('Provider');
        cy.get('#table-recurring > thead').contains('SubProvider');
        cy.get('#table-recurring > thead').contains('Name');
        cy.get('#table-recurring > thead').contains('Months');
        cy.get('#table-recurring > thead').contains('Speed');
        cy.get('#table-recurring > thead').contains('Bearer');
        cy.get('#table-recurring > thead').contains('Quantity');
        cy.get('#table-recurring > thead').contains('Price');
        cy.get('#table-recurring > thead').contains('Margin');
        cy.get('#table-recurring > thead').contains('Discount');
        cy.get('#table-recurring > thead').contains('Total');
    });
});
