import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp1 = getCurrentDate();
const product = { sku: generateHash(temp1), name: temp1 };

const temp2 = getCurrentDate();
const category = { code: generateHash(temp2), name: temp2 };

const temp3 = getCurrentDate();
const addonsProduct = { sku: generateHash(temp3), name: temp3 };

describe('Creates products and associates Category.cy', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    it('Creates a Category', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-categories').click();

        cy.get('#datatable-search').type(category.code);
        cy.wait(3000);
        cy.get('#categories-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#category-create-btn').click();

                cy.get('#category-name').type(category.name);
                cy.get('#category-code').type(category.code);
                cy.get('#btn-save-category').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Creates a Product and add category', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-products').click();

        cy.get('#datatable-search').type(product.sku);
        cy.wait(3000);
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();

                cy.get('#product-name').type(product.name);
                cy.get('#product-sku').type(product.sku);

                cy.selectLazyOption('product-categories', category.name);

                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Creates a Product and adds addons and links', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-products').click();

        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();

                cy.get('#product-name').type(addonsProduct.name);
                cy.get('#product-sku').type(addonsProduct.sku);

                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });

        cy.get('#datatable-search').type(addonsProduct.sku);
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();
        cy.get('#tabs-product-edit-2-2').click();
        cy.get('#btn-add-links').click();
        cy.get('#links-search-global').type(product.sku);
        cy.get('#links-table').contains(product.sku);
        cy.wait(1000);
        cy.get('#links-checkbox-0').click({ force: true });
        cy.get('#btn-relation-add-links').click({ force: true });
        cy.wait(1000);
        cy.get('#btn-relation-cancel-links').click();
        cy.get('#product-links-datatable-search').clear().type(product.sku);
        cy.wait(1000);
        cy.get('#product-links').contains(product.sku);
    });
});
