import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp1 = getCurrentDate();
const bundleParent = { sku: generateHash(temp1 + '-parent'), name: 'Bundle Parent ' + temp1 };

const temp2 = getCurrentDate();
const bundleItem = { sku: generateHash(temp2 + '-item'), name: 'Bundle Item ' + temp2 };

describe('Product Bundle Relations', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    it('Creates a parent product and a bundle item product', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-products').click();

        // Create bundle item product
        cy.get('#datatable-search').type(bundleItem.sku);
        cy.wait(1000);
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();
                cy.get('#product-name').type(bundleItem.name);
                cy.get('#product-sku').type(bundleItem.sku);
                cy.get('#price_plan_1').click();
                cy.get('#contract_term_value').type('3');
                cy.selectOption('#contract_term_unit', 'Years');
                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });

        // Create main (parent) product
        cy.get('#datatable-search').clear().type(bundleParent.sku);
        cy.wait(1000);
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();
                cy.get('#product-name').type(bundleParent.name);
                cy.get('#product-sku').type(bundleParent.sku);
                cy.get('#price_plan_1').click();
                cy.get('#contract_term_value').type('3');
                cy.selectOption('#contract_term_unit', 'Years');
                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Adds a bundle item to the parent product', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-products').click();

        cy.get('#datatable-search').type(bundleParent.sku);
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();

        // Navigate to Bundles tab (tab index 3)
        cy.get('#tabs-product-edit-3-3').click();

        // Open the Add Product modal for bundles
        cy.get('#btn-add-bundles').click();

        // Search for the bundle item and select it
        cy.get('#bundles-search-global').type(bundleItem.sku);
        cy.get('#bundles-table').should('not.contain', 'Loading');
        cy.get('#bundles-table').contains(bundleItem.sku);
        cy.get('[id^="bundles-checkbox-"]').first().click({ force: true });
        cy.get('#btn-relation-add-bundles').click({ force: true });
        cy.get('.notyf').contains('Products added successfully');
        cy.get('#btn-relation-cancel-bundles').click();

        // Verify the bundle item appears in the datatable
        cy.get('#product-bundles-datatable-search').clear().type(bundleItem.sku);
        cy.get('#product-bundles').should('not.contain', 'Loading');
        cy.get('#product-bundles').contains(bundleItem.sku);
    });

    it('Adds the parent product to a step for the quote journey', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-steps').click();
        cy.get('#datatable-search').type('Plans');
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#1-1').click();
        cy.selectLazyOption('select-product', bundleParent.name);
        cy.get('#button-add-product-to-step').click();
        cy.get('.notyf').contains('Product added with Success');
        cy.get('.p-datatable-table').contains(bundleParent.name);
    });

    it('Verifies the bundle badge and items list are shown in the mobile quote journey', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-edit').click();

        // Search for the bundle parent product in the step
        cy.get('#step-1-search-products').clear().type(bundleParent.sku);
        cy.wait(1000);

        // The product card for the bundle parent should show the "Bundle" badge
        cy.contains('.badge-bundle', 'Bundle').should('be.visible');

        // The bundle list header should be visible with item count
        cy.contains('Bundle includes').should('be.visible');

        // List starts collapsed — expand it first
        cy.contains('Show Items').click();
        cy.get('.bundle-list').should('be.visible');
        cy.contains(bundleItem.name).should('be.visible');

        // Hide Items toggle removes the list from the DOM (v-if)
        cy.contains('Hide Items').click();
        cy.get('.bundle-list').should('not.exist');
    });
});
