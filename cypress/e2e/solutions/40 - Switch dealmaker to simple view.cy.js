const { isSymbol } = require('lodash');
const { getCurrentDate, generateHash } = require('../../support/utils.js');

describe('Creates a Mobile Quote', () => {
    beforeEach(() => {
        cy.login('super@example.com', 'werwerwer');
    });

    it('Formats connectivity table to simple view', () => {
        cy.visit('/');
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
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectLazyOption('account_id');

        cy.get('#solution_id').select('Connectivity');
        cy.get('#input_address').type('EH32 9NT');
        cy.get('#select_address').should('be.visible').select(1);
        cy.get('#btn-create-quote').should('not.be.disabled').click();
        // Scroll back to top
        cy.scrollTo('top');
        //Provider filter
        cy.get('.p-multiselect').eq(0).click();

        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-checkbox').eq(0).click();
            });

        //SubProvider filter
        cy.get('.p-multiselect').eq(1).click();
        cy.wait(500);

        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-checkbox').eq(1).click();
            });

        //Add products to quote
        let keys = [0, 1, 2];
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click();
            cy.get('tbody tr button').eq(key).contains('Added');
        });
        //Save quote
        cy.get('.continue-btn').eq(0).click();
        cy.get('.Simple-item').its('length').should('eq', 6);
        cy.get('#table-simple > thead').contains('Quote Reference');
        cy.get('#table-simple > thead').contains('Speed');
        cy.get('#table-simple > thead').contains('Bearer');
        cy.get('#table-simple > thead').contains('Price Plan');
        cy.get('#table-simple > thead').contains('Months');
        cy.get('#table-simple > thead').contains('Price');
        cy.get('#table-simple > thead').contains('Total');
    });
    it('Formats connectivity table to DealMaker view', () => {
        cy.visit('/');
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
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.recurring-item').its('length').should('eq', 3);
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
