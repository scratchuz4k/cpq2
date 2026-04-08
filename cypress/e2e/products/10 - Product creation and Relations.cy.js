import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp1 = getCurrentDate();
const product = { sku: generateHash(temp1), name: temp1 };

const temp2 = getCurrentDate();
const category = { code: generateHash(temp2), name: temp2 };

const temp3 = getCurrentDate();
const addonsProduct = { sku: generateHash(temp3), name: temp3 };

const temp4 = getCurrentDate();
const sameStep = { name: 'same-step-' + temp4 };
const sameStepParent = { sku: generateHash(temp4 + '-parent'), name: 'Parent ' + temp4 };
const sameStepChild = { sku: generateHash(temp4 + '-child'), name: 'Child ' + temp4 };

describe('Creates products and associates Category.cy', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    it('Creates a Category and a Product with that category', () => {
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

describe('Validates same-step product link restriction', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    it('Creates a shared step and two products assigned to it', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-steps').click();
        cy.get('#step-create-btn').click();
        cy.get('#step-name').type(sameStep.name);
        cy.selectOption('#step-solution-type', 'Mobile');
        cy.get('#btn-save-step').click();
        cy.get('#datatable-search').type(sameStep.name);
        cy.wait(1000);
        cy.get('.p-datatable-table').contains(sameStep.name);

        cy.get('#settings').click();
        cy.get('#link-products').click();
        cy.get('#product-create-btn').click();
        cy.get('#product-name').type(sameStepParent.name);
        cy.get('#product-sku').type(sameStepParent.sku);
        cy.selectLazyOption('product-steps', sameStep.name);
        cy.get('#btn-save-product').click();
        cy.get('.notyf').contains('Created with Success');

        cy.get('#product-create-btn').click();
        cy.get('#product-name').type(sameStepChild.name);
        cy.get('#product-sku').type(sameStepChild.sku);
        cy.selectLazyOption('product-steps', sameStep.name);
        cy.get('#btn-save-product').click();
        cy.get('.notyf').contains('Created with Success');
    });

    it('Rejects linking a child product that shares a step with the parent', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-products').click();
        cy.get('#datatable-search').type(sameStepParent.sku);
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();
        cy.get('#tabs-product-edit-2-2').click();
        cy.get('#btn-add-links').click();
        cy.get('#links-search-sku').type(sameStepChild.sku);
        cy.wait(1000);
        cy.get('#links-table').contains(sameStepChild.sku);
        cy.get('#links-checkbox-0').click({ force: true });
        cy.get('#btn-relation-add-links').click({ force: true });
        cy.wait(1000);
        cy.get('.notyf').contains('The child product cannot be added to the same step as the parent product.');
        cy.get('#btn-relation-cancel-links').click();
        cy.get('#product-links-datatable-search').clear().type(sameStepChild.sku);
        cy.wait(1000);
        cy.get('#product-links').should('not.contain', sameStepChild.sku);
    });
});
