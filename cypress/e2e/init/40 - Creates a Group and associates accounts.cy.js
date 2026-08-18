const { getCurrentDate, generateHash, slugify } = require('../../support/utils.js');
const temp = getCurrentDate();
const user = { name: slugify(temp), email: slugify(temp) + '@email.com', password: generateHash(temp) };
const account1 = { ref: generateHash(temp) + '1', name: temp + '1' };
const account2 = { ref: generateHash(temp) + '2', name: temp + '2' };
const group = { name: temp + '-group' };

describe('Creates a Group and associates accounts.cy', () => {
    it('Creates  User', () => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/users');
        cy.get('#link-to-create-user').click();

        cy.get('#user-name').type(user.name);
        cy.get('#user-email').type(user.email);
        cy.get('#user-password').type(user.password);
        cy.selectLazyOption('user-role', 'Sales Rep');
        cy.get('#btn-save-user').click();
        cy.get('#datatable-search').type(user.name);
        cy.get('#users-table').contains(user.name);
    });
    it('Creates Accounts', () => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/accounts/create');
        cy.get('#account-name').type(account1.name);
        cy.get('#account-reference').type(account1.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
        cy.get('#account-create-btn').click();
        cy.get('#account-name').type(account2.name);
        cy.get('#account-reference').type(account2.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
    });
    it('Creates an Group, associates users and accounts', () => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#users-1-1').click();
        cy.get('#link-to-create-group').click();
        cy.wait(1000);
        cy.get('#group-create-name').type(group.name);
        cy.get('#group-create-submit').click();
        cy.get('.notyf').contains('Created with Success');
        cy.get('#datatable-search').type(group.name);
        cy.get('#roles-table').contains(group.name);
        cy.get('#datatable-btn-0-edit').click();
        cy.wait(1000);
        cy.get('#1-1').click();
        cy.selectLazyOption('select-user', user.name);
        cy.get('#button-add-user-to-group').click();
        cy.get('.notyf').contains('Created with Success');
        cy.get('#2-2').click();
        cy.selectLazyOption('select-account', account1.name);
        cy.get('#button-add-account-to-group').click();
        cy.get('.notyf').contains('Created with Success');
    });
    it('check if user has correct accounts', () => {
        cy.login(user.email, user.password);
        cy.visit('/quotes/create');
        cy.get('#input-select-account_id').click();
        cy.get('#select-account_id-search').type(account1.name);
        cy.wait(1000);
        cy.get('#account_id-sidebar').contains(account1.name);
        cy.get('#select-account_id-search').clear();
        cy.get('#select-account_id-search').type(account2.name);
        cy.get('#account_id-sidebar').should('not.contain', account2.name);
    });
});
