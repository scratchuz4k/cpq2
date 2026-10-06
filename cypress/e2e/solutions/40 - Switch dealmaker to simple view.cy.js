const { isSymbol } = require('lodash');

const provider = 'Giacom';
const subprovider = 'BT';

describe('Creates a Mobile Quote', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Turns on the quote summary simple view (tenant settings)', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#1-1').click();

        cy.selectOption('#quote-simple-view', 'Simple');
        cy.wait(1000);

        cy.get('.notyf').contains('Content updated successfully');
    });

    it('Creates Connectivity quote and has simple Table', () => {
        cy.visit('/quotes/create');
        cy.selectLazyOption('account_id');
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('.step-action-bar #btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();
        cy.dismissLoadingModal();

        //Managed filter
        cy.get('#input-select-isManaged-filter .trigger-select').click();
        cy.wait(500);
        cy.get('#input-select-isManaged-filter .options-container').within(() => {
            cy.get('.option').contains('No').click();
        });

        //Provider filter
        cy.get('#input-select-providerName-filter .trigger-select').click();
        cy.wait(500);
        cy.get('#input-select-providerName-filter .options-container').within(() => {
            cy.get('.option').contains(provider).click();
        });

        //SubProvider filter
        cy.get('#input-select-connectivitySupplierGroup-filter .trigger-select').click();
        cy.wait(500);
        cy.get('#input-select-connectivitySupplierGroup-filter .options-container').within(() => {
            cy.get('.option').contains(subprovider).click();
        });
        cy.get('#input-select-connectivitySupplierGroup-filter .trigger-select').click();

        // Scroll back to top
        cy.scrollTo('top');
        cy.contains('.datatable-table-header-text', 'Set Up').click();
        cy.wait(1000);
        cy.contains('.datatable-table-header-text', 'Set Up').click();
        cy.wait(1000);

        //Add products to quote

        cy.get('tbody tr button').eq(0).click();
        cy.get('#connectivity-product-1').contains('Primary Product');

        //Save quote
        cy.get('#btn-continue').click();
        cy.get('.Simple-item').its('length').should('eq', 2);
        cy.get('#table-simple > thead').contains('Quote Reference');
        cy.get('#table-simple > thead').contains('Speed');
        cy.get('#table-simple > thead').contains('Bearer');
        cy.get('#table-simple > thead').contains('Price Plan');
        cy.get('#table-simple > thead').contains('Term');
        cy.get('#table-simple > thead').contains('Frequency');
        cy.get('#table-simple > thead').contains('Price');
        cy.get('#table-simple > thead').contains('Total');

        cy.get('#table-recurring').should('not.exist');
        cy.get('.commission-calculator').should('not.exist');
    });

    it('Turns off the quote summary simple view (tenant settings)', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#1-1').click();

        cy.selectOption('#quote-simple-view', 'DealMaker');
        cy.wait(1000);

        cy.get('.notyf').contains('Content updated successfully');
    });

    it('Check Connectivity quote and has DealMaker Table', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.recurring-item').its('length').should('eq', 1);
        cy.get('#table-recurring > thead').contains('Product');
        cy.get('#table-recurring > thead').contains('Discount');
        cy.get('#table-recurring > thead').contains('Margin');
        cy.get('#table-recurring > thead').contains('Unit price');
        cy.get('#table-recurring > thead').contains('Qty');
        cy.get('#table-recurring > thead').contains('Billing freq');
        cy.get('#table-recurring > thead').contains('Term');
        cy.get('#table-recurring > thead').contains('Total');
        cy.get('#table-breakdown > thead').contains('Discount value');

        cy.get('.commission-calculator').first().should('be.visible');
    });
});
