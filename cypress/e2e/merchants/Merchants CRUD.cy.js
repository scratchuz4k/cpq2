import { generateHash, getCurrentDate } from '../../support/utils.js';

const merchant_name = 'Merchant - ' + generateHash(getCurrentDate());
const updated_merchant_name = 'Updated ' + merchant_name;
describe('Create Order Manager User.cy', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    it('Create a Merchant', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-merchants').click();
        cy.get('#merchant-create-btn').click();

        cy.get('#merchant-name').type(merchant_name);

        cy.get('#btn-save-merchant').click();
        cy.get('#datatable-search').type(merchant_name);
        cy.get('.p-datatable-table').contains(merchant_name);
    });

    it('Edit a Merchant', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-merchants').click();

        cy.get('#datatable-btn-0-edit').click();

        cy.get('#merchant-name').clear().type(updated_merchant_name);

        cy.get('#btn-save-merchant').click();
        cy.get('#datatable-search').type(updated_merchant_name);
        cy.get('.p-datatable-table').contains(updated_merchant_name);
    });

    it('Delete a Merchant', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-merchants').click();

        cy.get('#datatable-search').type(updated_merchant_name);
        cy.get('#datatable-btn-0-delete').click();

        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Removed with success');
    });
});
