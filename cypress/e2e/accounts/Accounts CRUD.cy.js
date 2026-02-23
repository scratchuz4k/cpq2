import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };
const updated_account = { ref: account.ref + '_updated', name: account.name + '_updated' };

describe('Accounts Crud', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it.only('Create an Account', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-accounts').click();
        cy.get('#account-create-btn').click();
        cy.wait(1000);
        cy.get('#account-name').should('be.visible').type(account.name);
        cy.get('#account-reference').type(account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
    });

    it('Update an Account', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-accounts').click();
        cy.get('#datatable-search').type(account.name);
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#account-name').type(updated_account.name);
        cy.get('#account-reference').type(updated_account.ref);
        cy.get('#btn-update-account').click();
        cy.get('.notyf').contains('Updated with Success');
    });

    it('Delete an Account', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-accounts').click();
        cy.get('#datatable-search').type(updated_account.name);
        cy.wait(500);
        cy.get('#datatable-btn-0-delete').click();

        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Removed with success');
    });
});
