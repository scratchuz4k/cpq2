import { generateHash, getCurrentDate } from '../../support/utils.js';
import { forEach } from 'lodash';

const temp = getCurrentDate();
const steps = [{ name: 'step-1' + temp }, { name: 'step-2' + temp }];
const products = [
    { sku: 'procuct-1' + generateHash(temp), name: 'Procuct 1' + temp },
    { sku: 'procuct-2' + generateHash(temp), name: 'Procuct 2' + temp }
];
describe('Manage Products Step relation', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    steps.forEach((step, index) => {
        it('Create a Step', () => {
            cy.visit('/');
            cy.get('#settings').click();
            cy.get('#link-steps').click();
            cy.get('#step-create-btn').click();
            cy.get('#step-name').type(step.name);
            cy.selectOption('#step-solution-type', 'Mobile');
            cy.get('#btn-save-step').click();
            cy.get('#datatable-search').type(step.name);
            cy.get('.p-datatable-table').contains(step.name);
        });
    });

    products.forEach((product, index) => {
        it('Creates a Product', () => {
            cy.visit('/');
            cy.get('#settings').click();
            cy.get('#link-products').click();

            cy.get('#product-create-btn').click();
            cy.wait(1000);
            cy.get('#product-sku').type(product.sku);
            cy.get('#product-name').type(product.name);

            cy.get('#price_plan_1').click();
            cy.get('#contract_term_value').type('100');
            cy.selectOption('#contract_term_unit', 'Days');

            cy.get('#btn-save-product').click();
            cy.get('.notyf').contains('Created with Success');

            //Add procuct to step
            cy.addProductToStep(steps[0].name, product.name);
            cy.addProductToStep(steps[1].name, product.name);
        });
    });

    steps.forEach((step, index) => {
        it('Check Step has related product', () => {
            cy.visit('/');
            cy.get('#settings').click();
            cy.get('#link-steps').click();
            cy.get('#datatable-search').type(step.name);
            cy.wait(1000);
            cy.get('#datatable-btn-0-edit').click();

            cy.get('#1-1').click();
            cy.get('.p-datatable-table').contains(products[index].name);
        });
    });

    it('Update Step solution type', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-steps').click();
        cy.get('#datatable-search').type(steps[0].name);
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();
        cy.selectOption('#step-solution-type', 'Cloud Audit');
        cy.get('#btn-save-step').click();
        cy.get('#datatable-search').type(steps[0].name);
        cy.get('.p-datatable-table').contains(steps[0].name);
        cy.get('.p-datatable-table').contains('Cloud Audit');
    });

    it('Delete Step', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-steps').click();
        cy.get('#datatable-search').type(steps[0].name);
        cy.wait(1000);
        cy.get('#datatable-btn-0-delete').click();

        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Removed with success');

        cy.wait(1000);
        cy.get('#datatable-search').type(steps[0].name);
        cy.get('#steps-table').should('not.contain', steps[0].name);
    });

    it('Confirm that Solution Type no longer has Step associated', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-solutions').click();

        cy.get('#datatable-search').type('Cloud Audit');
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#1-1').click();

        cy.get('#steps-table').should('not.contain', steps[0].name);
    });

    products.forEach((product, index) => {
        it('Confirm that Product no longer has Step associated', () => {
            cy.visit('/');
            cy.get('#settings').click();
            cy.get('#link-products').click();

            cy.get('#products-table-datatable-search').type(product.sku);
            cy.wait(1000);
            cy.get('#products-table').should('not.contain', steps[0].name);
        });
    });
});
