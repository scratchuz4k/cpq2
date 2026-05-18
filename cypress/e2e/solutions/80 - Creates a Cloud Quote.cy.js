const { getCurrentDate, generateHash } = require('../../support/utils.js');
const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };

const temp1 = getCurrentDate();
const product = { sku: generateHash(temp1), name: temp1 };

const temp2 = getCurrentDate();
const step = { name: 'step-1' + temp2 };

describe('Creates a Cloud Quote', () => {
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

    it('Creates a Step', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-steps').click();

        cy.get('#datatable-search').type(step.name);
        cy.wait(1000);
        cy.get('#steps-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#step-create-btn').click();
                cy.get('#step-name').type(step.name);
                cy.selectOption('#step-solution-type', 'Cloud');
                cy.get('#btn-save-step').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Creates a Product and add step', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-products').click();

        cy.get('#datatable-search').type(product.sku);
        cy.wait(1000);
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();

                cy.get('#product-name').type(product.name);
                cy.get('#product-sku').type(product.sku);

                cy.get('#contract_term_value').type('100');
                cy.selectOption('#contract_term_unit', 'Days');

                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');

                cy.get('#settings').click();
                cy.get('#link-steps').click();
                cy.get('#datatable-search').type(step.name);
                cy.wait(1000);
                cy.get('#datatable-btn-0-edit').click();

                cy.get('#1-1').click();
                cy.selectLazyOption('select-product', product.name);
                cy.get('#button-add-product-to-step').click();
                cy.get('.notyf').contains('Product added with Success');
                cy.get('.p-datatable-table').contains(product.name);
            }
        });
    });

    it('Creates Cloud Quote', () => {
        cy.visit('/quotes/create');
        cy.selectLazyOption('account_id', account.name);
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-cloud').click();
        cy.wait(1000);
        cy.get('#btn-create-quote').click();
        cy.wait(2000);
        cy.addProductAndVerifySummary('#btn-product-0-add-1').click();
        cy.wait(1000);
        cy.addProductAndVerifySummary('#btn-product-1-add-1').click();
        cy.wait(1000);

        cy.get('#current-step')
            .invoke('text')
            .then((currentStepText) => {
                cy.get('#last-step')
                    .invoke('text')
                    .then((lastStepText) => {
                        const current_step = parseInt(currentStepText.trim());
                        const last_step = parseInt(lastStepText.trim());

                        for (let i = current_step; i <= last_step; i++) {
                            cy.get(`#btn-step-${i}-next`).click();
                        }
                    });
            });

        cy.wait(2000);

        cy.get('.recurring-item').should('have.length', 2);
    });
});
