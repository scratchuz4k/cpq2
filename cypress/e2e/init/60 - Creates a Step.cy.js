const { getCurrentDate, generateHash, slugify } = require('../../support/utils.js');
const temp = getCurrentDate();

const step = {
    name: 'step' + temp,
    solution_type: 'Mobile'
};

describe('Creates a Step and associate it with Mobile Solution Type', () => {
    it('Create a Step', () => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-steps').click();
        cy.get('#step-create-btn').click();
        cy.get('#step-name').type(step.name);
        cy.selectOption('#step-solution-type', step.solution_type);
        cy.get('#btn-save-step').click();
        cy.get('#datatable-search').type(step.name);
        cy.get('.p-datatable-table').contains(step.name);
    });
});
