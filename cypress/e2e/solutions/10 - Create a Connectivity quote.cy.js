import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login('super@example.com', 'werwerwer');
    });

    it('Creates an Account', () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.get('#link-to-create-account').click();
        cy.get('#account-name').type(account.name);
        cy.get('#account-reference').type(account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
    });
    it('Create a Connectivity Quote', () => {
        cy.visit('/');
        cy.get('#create-tab').click();

        cy.selectLazyOption('account_id', account.name);
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('#btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.get('#select_address').should('be.visible').select(1);
        cy.get('#btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();

        // Scroll back to top
        cy.scrollTo('top');
        //Provider filter
        cy.get('#provider-multiselect').click();

        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-checkbox').eq(2).click();
            });

        //SubProvider filter
        cy.get('#subprovider-multiselect').click();
        cy.wait(500);

        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-checkbox').eq(5).click();
            });
        cy.wait(1000);

        //Add products to quote
        let keys = [0, 1, 2, 3, 4];
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click();
            cy.get('tbody tr button').eq(key).contains('Added');
        });

        //Save quote
        cy.get('.continue-btn').eq(0).click();
        cy.get('.recurring-item').its('length').should('eq', 5);
        cy.get('.one_off-item').its('length').should('eq', 5);
    });
    it('I can add a Custom Line and check quoted items', () => {
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#btn-recurring-add').click();
        cy.get('#input-new-recurring-name').type('Custom line recurring', {
            force: true
        });
        cy.get('#input-new-recurring-quantity').type(10, { force: true });
        cy.get('#input-new-recurring-price').type(10, { force: true });
        cy.get('#input-new-recurring-discount').type(10, { force: true });
        cy.get('#btn-new-recurring-add').click({ force: true });
        cy.get('#table-recurring').contains('Custom line recurring');

        cy.get('#btn-one_off-add').click();
        cy.get('#input-new-one_off-name').type('Custom line one_off', {
            force: true
        });
        cy.get('#input-new-one_off-quantity').type(10, { force: true });
        cy.get('#input-new-one_off-price').type(10, { force: true });
        cy.get('#input-new-one_off-discount').type(10, { force: true });
        cy.get('#btn-new-one_off-add').click({ force: true });
        cy.get('#table-one_off').contains('Custom line one_off');

        cy.get('#btn-previous').click();
        cy.get('#btn-more-filters').click();
        cy.get('#filter-show-quoted .p-toggleswitch-input').click({
            force: true
        });
        cy.wait(3000);
        cy.get('.connectivity-table-item')
            .its('length')
            .then((items) => {
                expect(items).to.equal(5);
            });
    });

    it('I can filter products by contended', () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectLazyOption('account_id', account.name);
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('#btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.get('#select_address').should('be.visible').select(1);
        cy.get('#btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();

        cy.scrollTo('top');

        cy.get('#btn-more-filters').click();
        cy.get('#filter-show-contended .custom-range-input').invoke('val', 100).trigger('input', { force: true });
        cy.wait(3000);
        cy.get('table tbody tr.connectivity-table-item').then(($rows) => {
            cy.get('.is-contended').should('have.length', $rows.length);
        });

        cy.get('#btn-more-filters').click();

        let keys = [0, 1, 2, 3, 4];
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click();
            cy.get('tbody tr button').eq(key).contains('Added');
        });
    });

    it('I can Create an order from a quote', () => {
        cy.visit('/');
        cy.get('#datatable-btn-0-crete-order').click();
        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Order created with success');
    });
});
