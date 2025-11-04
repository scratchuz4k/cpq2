import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };
const price = { install: null, price: null };
const supplier = 'Gamma';

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    it('Get Price From Gamma Product', () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectFirstOption('account_id');
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('#btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('#btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();

        cy.wait(1000);
        //Provider filter
        cy.get('#provider-multiselect').click();
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-checkbox').eq(1).click();
            });

        cy.get('#table-header-setup').click();
        cy.wait(1000);
        cy.get('#table-header-setup').click();
        cy.wait(1000);

        cy.get('#line-0-price-install').then(($input) => {
            price.install = $input.text().trim();
            cy.log(price.install);
        });

        cy.get('#line-0-price-monthly').then(($input) => {
            price.monthly = $input.text().trim();
            cy.log(price.monthly);
        });
    });

    it('Create Supplier Gamma Products', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-suppliers').click();
        cy.get('#datatable-search').type(supplier);
        cy.wait(1000);
        cy.get('#suppliers-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#supplier-create-btn').click();
                cy.get('#supplier-name').type(supplier);
                cy.get('#supplier-reference').type(supplier);
                cy.get('#btn-save-supplier').click();
                cy.get('.p-row-even').should('have.length.greaterThan', 0);
            }
        });
    });
    it('Create SPA for Gamma Products', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-spas').click();
        cy.get('#create-spa').click();
        cy.get('#spa-entity-Supplier').click();
        cy.selectLazyOption('Supplier1', supplier);

        cy.get('#spa-percentage').type(20);
        cy.get('#save-spa').click();
        cy.get('.notyf').contains('Created with Success');
    });

    it('Check Price New Price For Gamma Product', () => {
        cy.visit('/');
        cy.get('#datatable-btn-0-edit').click();

        cy.wait(1000);
        //Provider filter
        cy.get('#provider-multiselect').click();
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-checkbox').eq(1).click();
            });

        cy.get('#table-header-setup').click();
        cy.wait(1000);
        cy.get('#table-header-setup').click();
        cy.wait(1000);

        cy.get('#line-0-price-install').then(($input) => {
            expect($input.text().trim()).to.not.equal(price.install);
        });
        cy.get('#line-0-price-monthly').then(($input) => {
            expect($input.text().trim()).to.not.equal(price.monthly);
        });
    });

    it('Delete SPA ', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-spas').click();
        cy.get('#datatable-btn-0-delete').click();
        cy.get('#swal-btn-confirm').click();
    });
});
