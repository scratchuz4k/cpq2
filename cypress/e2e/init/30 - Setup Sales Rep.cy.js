const crypto = require('crypto');
const { getCurrentDate, generateHash, slugify } = require('../../support/utils.js');
const temp = getCurrentDate();
const user = { name: slugify(temp), email: slugify(temp) + '@email.com', password: generateHash(temp) };
const account = { ref: generateHash(temp), name: temp };

describe('Setup Sales Rep.cy', () => {
    it('Creates an User', () => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#link-to-create-user').click();

        cy.get('#user-name').type(user.name);
        cy.get('#user-email').type(user.email);
        cy.get('#user-password').type(user.password);
        cy.selectLazyOption('user-role', 'Sales Rep');
        cy.get('#user-commission-rate').type(5);
        cy.get('#btn-save-user').click();
        cy.get('#datatable-search').type(user.name);
        cy.get('#users-table').contains(user.name);
    });
    it('Creates an Order and checks commission', () => {
        cy.login(user.email, user.password);
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-accounts').click();
        cy.get('#account-create-btn').click();
        cy.get('#account-name').type(account.name);
        cy.get('#account-reference').type(account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
        cy.visit('/quotes/create');
        cy.selectLazyOption('account_id', account.name);
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.comission .value')
            .first()
            .should('be.visible')
            .invoke('text')
            .then((text) => {
                expect(text).to.not.equal(5);
            });
    });
});
