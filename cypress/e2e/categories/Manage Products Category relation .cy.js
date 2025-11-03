import { generateHash, getCurrentDate } from '../../support/utils.js';
import { forEach } from 'lodash';

const temp = getCurrentDate();
const categories = [
    { code: 'cat-1' + generateHash(temp), name: 'cat-1' + temp },
    { code: 'cat-2' + generateHash(temp), name: 'cat-2' + temp }
];
const products = [
    { sku: 'procuct-1' + generateHash(temp), name: 'Procuct 1' + temp },
    { sku: 'procuct-2' + generateHash(temp), name: 'Procuct 2' + temp }
];
describe('Manage Products Category relation', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    categories.forEach((category, index) => {
        it('Create a Category', () => {
            cy.visit('/');
            cy.get('#my-account').click();
            cy.get('#link-categories').click();
            cy.get('#category-create-btn').click();
            cy.get('#category-code').type(category.code);
            cy.get('#category-name').type(category.name);
            cy.get('#btn-save-category').click();
            cy.get('#datatable-search').type(category.name);
            cy.get('.p-datatable-table').contains(category.name);
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

            cy.selectLazyOption('product-categories', categories[index].name);

            cy.get('#btn-save-product').click();
            cy.get('.notyf').contains('Created with Success');

            cy.get('#datatable-search').type(product.sku);
            cy.get('.p-datatable-table').contains(product.name);
        });
    });

    categories.forEach((category, index) => {
        it('Check Category has related product', () => {
            cy.visit('/');
            cy.get('#my-account').click();
            cy.get('#link-categories').click();
            cy.get('#datatable-search').type(category.name);
            cy.wait(1000);
            cy.get('#datatable-btn-0-edit').click();

            cy.get('#1-1').click();
            cy.get('.p-datatable-table').contains(products[index].name);
        });
    });
});
