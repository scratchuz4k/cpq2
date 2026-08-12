import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };
const provider = 'Giacom';
const subprovider = 'BT';
const connectivityType = 'Ethernet';
const technologyType = 'Fibre';

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Creates an Account', () => {
        cy.visit('/accounts/create');
        cy.get('#account-name').type(account.name);
        cy.get('#account-reference').type(account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
    });
    it('Create a Connectivity Quote', () => {
        cy.visit('/quotes/create');

        cy.selectLazyOption('account_id', account.name);
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('.step-action-bar #btn-next-step').click();
        cy.get('.step-action-bar #btn-create-quote').should('not.be.disabled').click();
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

        //connectivityType filter
        cy.get('#input-select-connectivityType-filter .trigger-select').click();
        cy.wait(500);
        cy.get('#input-select-connectivityType-filter .options-container').within(() => {
            cy.get('.option').contains(connectivityType).click();
        });
        cy.get('#input-select-connectivityType-filter .trigger-select').click();

        // Scroll back to top
        cy.scrollTo('top');
        cy.contains('.datatable-table-header-text', 'Set Up').click();
        cy.wait(1000);
        cy.contains('.datatable-table-header-text', 'Set Up').click();
        cy.wait(1000);

        cy.get('tbody tr button').eq(0).click();
        cy.get('#connectivity-product-1').contains('Primary Product');

        //Save quote
        cy.get('#btn-continue').click();
        cy.get('.recurring-item').its('length').should('eq', 1);
        cy.get('.one_off-item').its('length').should('eq', 1);
    });
    // it('I can add a Custom Line and check quoted items', () => {
    //     cy.visit('/quotes');
    //     cy.get('#datatable-btn-0-show').click();
    //     cy.get('#btn-recurring-add').click();
    //     cy.get('#input-new-recurring-name').type('Custom line recurring', {
    //         force: true
    //     });
    //     cy.get('#input-new-recurring-quantity').type(10, { force: true });
    //     cy.get('#input-new-recurring-price').type(10, { force: true });
    //     cy.get('#input-new-recurring-discount').type(10, { force: true });
    //     cy.get('#btn-new-recurring-add').click({ force: true });
    //     cy.get('#table-recurring').contains('Custom line recurring');

    //     cy.get('#btn-one_off-add').click();
    //     cy.get('#input-new-one_off-name').type('Custom line one_off', {
    //         force: true
    //     });
    //     cy.get('#input-new-one_off-quantity').type(10, { force: true });
    //     cy.get('#input-new-one_off-price').type(10, { force: true });
    //     cy.get('#input-new-one_off-discount').type(10, { force: true });
    //     cy.get('#btn-new-one_off-add').click({ force: true });
    //     cy.get('#table-one_off').contains('Custom line one_off');
    // });
    // it('I can filter products by contended', () => {
    //     cy.visit('/quotes/create');
    //     cy.selectLazyOption('account_id', account.name);
    //     cy.get('.step-action-bar #btn-next-step').click();

    //     cy.get('#solution_type-connectivity').click();
    //     cy.get('.step-action-bar #btn-next-step').click();

    //     cy.get('#input_address').type('EH32 9NT');
    //     cy.selectOption('#select_address', 1);
    //     cy.get('.step-action-bar #btn-next-step').click();
    //     cy.get('.step-action-bar #btn-create-quote').should('not.be.disabled').click();

    //     cy.scrollTo('top');

    //     cy.get('#btn-more-filters').click();
    //     cy.get('#filter-show-contended .custom-range-input').invoke('val', 100).trigger('input', { force: true });
    //     cy.wait(3000);
    //     cy.get('table tbody tr.connectivity-table-item').then(($rows) => {
    //         cy.get('.is-contended').should('have.length', $rows.length);
    //     });

    //     cy.get('#btn-more-filters').click();

    //     cy.get('tbody tr button').eq(0).click();
    //     cy.get('#connectivity-product-1').contains('Primary Product');
    // });
    it("I can see one product per uploadSpeed with 'Show only lowest cost products' option selected", () => {
        cy.visit('/quotes/create');
        cy.selectLazyOption('account_id', account.name);
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('.step-action-bar #btn-next-step').click();
        cy.get('.step-action-bar #btn-create-quote').should('not.be.disabled').click();
        cy.dismissLoadingModal();

        cy.scrollTo('top');

        cy.get("#filter-lower-cost input[type='checkbox']").should('be.checked');
        cy.wait(3000);

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
            cy.get('.option').contains('Gamma').click();
        });

        //SubProvider filter
        cy.get('#input-select-connectivitySupplierGroup-filter .trigger-select').click();
        cy.wait(500);
        cy.get('#input-select-connectivitySupplierGroup-filter .options-container').within(() => {
            cy.get('.option').contains('TalkTalk').click();
        });

        //Contract length filter
        cy.get('#input-select-contractTermMonths-filter .trigger-select').click();
        cy.wait(500);
        cy.get('#input-select-contractTermMonths-filter .options-container').within(() => {
            cy.get('.option').contains('36').click();
        });

        //Bearer filter
        cy.get('#input-select-bearer-filter .trigger-select').click();
        cy.wait(500);
        cy.get('#input-select-bearer-filter .options-container').within(() => {
            cy.get('.option').contains('1 Gb').click();
        });

        //Speed filter
        cy.get('#input-select-formattedSpeed-filter .trigger-select').click();
        cy.wait(500);
        cy.get('#input-select-formattedSpeed-filter .options-container').within(() => {
            cy.get('.option').contains('500 Mb').click();
        });

        cy.scrollTo('top');
        cy.contains('.datatable-table-header-text', 'Speed').click();
        cy.wait(1000);
        cy.contains('.datatable-table-header-text', 'Speed').click();
        cy.wait(1000);

        cy.get('table tbody tr')
            .filter(':contains("Internet Access (Wires Only)")')
            .filter(':contains("1 Gb")')
            .filter(':contains("500 Mb")')
            .should('have.length', 2);

        cy.get('tbody tr button').eq(0).click();
        cy.get('#connectivity-product-1').contains('Primary Product');
    });
});
