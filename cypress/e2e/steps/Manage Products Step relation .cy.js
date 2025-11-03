import { generateHash, getCurrentDate } from '../../support/utils.js';
import { forEach } from 'lodash';

const temp = getCurrentDate();
const steps = [
    { code: 'step-1' + generateHash(temp), name: 'step-1' + temp },
    { code: 'step-2' + generateHash(temp), name: 'step-2' + temp }
];
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
            cy.get('#my-account').click();
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
            cy.get('#my-account').click();
            cy.get('#link-products').click();

            cy.get('#product-create-btn').click();

            cy.get('#product-sku').type(product.sku);
            cy.get('#product-name').type(product.name);

            cy.selectLazyOption('product-step', steps[index].name);

            cy.get('#btn-save-product').click();
            cy.get('.notyf').contains('Created with Success');

            cy.get('#datatable-search').type(product.sku);
            cy.get('.p-datatable-table').contains(product.name);
        });
    });

    steps.forEach((step, index) => {
        it('Check Step has related product', () => {
            cy.visit('/');
            cy.get('#my-account').click();
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
        cy.get('#my-account').click();
        cy.get('#link-steps').click();
        cy.get('#datatable-search').type(steps[0].name);
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();
        cy.selectOption('#step-solution-type', 'Hardware');
        cy.get('#btn-save-step').click();
        cy.get('#datatable-search').type(steps[0].name);
        cy.get('.p-datatable-table').contains(steps[0].name);
        cy.get('.p-datatable-table').contains('Hardware');
    });
});
