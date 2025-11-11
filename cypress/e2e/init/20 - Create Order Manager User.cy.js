const { getCurrentDate, generateHash, slugify } = require('../../support/utils.js');
const temp = getCurrentDate();
const user = { name: slugify(temp), email: slugify(temp) + '@email.com', password: generateHash(temp) };

describe('Create Order Manager User.cy', () => {
    it('Creates Order Manager User', () => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/users');
        cy.get('#link-to-create-user').click();

        cy.get('#user-name').type(user.name);
        cy.get('#user-email').type(user.email);
        cy.get('#user-password').type(user.password);
        cy.selectLazyOption('user-role', 'Order Manager');
        cy.get('#btn-save-user').click();
        cy.get('#datatable-search').type(user.name);
        cy.get('#users-table').contains(user.name);
    });
});
