const crypto = require('crypto');
const { getCurrentDate, generateHash, slugify } = require('../../support/utils.js');
const temp = getCurrentDate();
const user = { name: slugify(temp), email: slugify(temp) + '@email.com', password: generateHash(temp) };
const account = { ref: generateHash(temp), name: temp };

describe('Activate orders flow.cy', () => {
    it('Activate orders flow', () => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/quotes');
        cy.get('#my-account').click();
        cy.get('#link-tenant').click();
        cy.get('#1-1').click();
        cy.get('#active-orders-flow > input').then(($checkbox) => {
            if (!$checkbox.is(':checked')) {
                cy.wrap($checkbox).check();
            }
        });
        cy.wait(500);
        cy.get('.notyf').contains('Content updated successfully');
    });
});
