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
        cy.visit('/quotes/create');
        cy.get('#link-to-create-account').click();
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

                cy.selectLazyOption('product-steps', step.name);

                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Creates Cloud Quote', () => {
        cy.visit('/quotes/create');
        cy.selectLazyOption('account_id', account.name);
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-cloud').click();
        cy.get('#btn-create-quote').click();
        cy.wait(1000);
        cy.get('#btn-product-0-add-25').click();
        cy.wait(1000);
        cy.get('#btn-product-1-add-25').click();
        cy.wait(1000);
        cy.get('#btn-step-1-next').click();
        cy.wait(1000);
        cy.get('#btn-product-0-add-25').click();
        cy.wait(1000);
        cy.get('#btn-step-2-next').click();

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

        cy.get('.recurring-item').should('have.length', 3);
    });

    it('I can add and delete a Custom Line', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#btn-recurring-add').click();
        cy.get('#input-new-recurring-name').type('Custom line recurring');
        cy.get('#input-new-recurring-quantity').type(10);
        cy.get('#input-new-recurring-price').type(10);
        cy.get('#input-new-recurring-discount').type(10);
        cy.get('#btn-new-recurring-add').click();
        cy.get('#table-recurring').contains('Custom line recurring');

        cy.get('#btn-one_off-add').click();
        cy.get('#input-new-one_off-name').type('Custom line one_off');
        cy.get('#input-new-one_off-quantity').type(10);
        cy.get('#input-new-one_off-price').type(10);
        cy.get('#input-new-one_off-discount').type(10);
        cy.get('#btn-new-one_off-add').click();
        cy.get('#table-one_off').contains('Custom line one_off');

        cy.get('.recurring-item')
            .its('length')
            .then((count) => {
                cy.get(`#btn-recurring-${count - 1}-remove`).click();
            });
        cy.get('#swal-btn-confirm').click();
        cy.get('.one_off-item')
            .its('length')
            .then((count) => {
                cy.get(`#btn-one_off-${count - 1}-remove`).click();
            });
        cy.get('#swal-btn-confirm').click();
    });
});
