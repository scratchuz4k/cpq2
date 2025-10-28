import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };
const provider = 'Giacom';
const subprovider = 'BT';

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });

    it('Creates an Account', () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.get('#link-to-create-account').click();
        cy.get('#account-name').type(account.name);
        cy.get('#account-reference').type(account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
    });
    it('Create a Connectivity Quote', () => {
        cy.visit('/');
        cy.get('#create-tab').click();

        cy.selectLazyOption('account_id', account.name);
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('#btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('#btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();

        //Provider filter
        cy.get('#provider-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains(provider).click();
            });

        //SubProvider filter
        cy.get('#subprovider-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains(subprovider).click();
            });

        // Scroll back to top
        cy.scrollTo('top');
        cy.get('#table-header-setup').click();
        cy.wait(1000);
        cy.get('#table-header-setup').click();
        cy.wait(1000);

        cy.get('tbody tr button').eq(0).click();
        cy.get('#connectivity-product-1').contains('Primary Product');

        //Save quote
        cy.get('.continue-btn').eq(0).click();
        cy.get('.recurring-item').its('length').should('eq', 1);
        cy.get('.one_off-item').its('length').should('eq', 1);
    });
    it('I can add a Custom Line and check quoted items', () => {
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#btn-recurring-add').click();
        cy.get('#input-new-recurring-name').type('Custom line recurring', {
            force: true
        });
        cy.get('#input-new-recurring-quantity').type(10, { force: true });
        cy.get('#input-new-recurring-price').type(10, { force: true });
        cy.get('#input-new-recurring-discount').type(10, { force: true });
        cy.get('#btn-new-recurring-add').click({ force: true });
        cy.get('#table-recurring').contains('Custom line recurring');

        cy.get('#btn-one_off-add').click();
        cy.get('#input-new-one_off-name').type('Custom line one_off', {
            force: true
        });
        cy.get('#input-new-one_off-quantity').type(10, { force: true });
        cy.get('#input-new-one_off-price').type(10, { force: true });
        cy.get('#input-new-one_off-discount').type(10, { force: true });
        cy.get('#btn-new-one_off-add').click({ force: true });
        cy.get('#table-one_off').contains('Custom line one_off');

        // cy.get('#btn-previous').click();
        // cy.get('#btn-more-filters').click();
        // cy.get('#filter-show-quoted .p-toggleswitch-input').click({
        //     force: true
        // });
        // cy.wait(3000);
        // cy.get('.connectivity-table-item')
        //     .its('length')
        //     .then((items) => {
        //         expect(items).to.equal(1);
        //     });
    });

    it('I can filter products by contended', () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectLazyOption('account_id', account.name);
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('#btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('#btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();

        cy.scrollTo('top');

        cy.get('#btn-more-filters').click();
        cy.get('#filter-show-contended .custom-range-input').invoke('val', 100).trigger('input', { force: true });
        cy.wait(3000);
        cy.get('table tbody tr.connectivity-table-item').then(($rows) => {
            cy.get('.is-contended').should('have.length', $rows.length);
        });

        cy.get('#btn-more-filters').click();

        cy.get('tbody tr button').eq(0).click();
        cy.get('#connectivity-product-1').contains('Primary Product');
    });

    it("I can see one product per uploadSpeed with 'Show only lowest cost products' option selected", () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectLazyOption('account_id', account.name);
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('#btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('#btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();

        cy.scrollTo('top');

        cy.get('#btn-more-filters').click();
        cy.get("#filter-lower-cost input[type='checkbox']").should('be.checked');
        cy.wait(3000);

        //Provider filter
        cy.get('#provider-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains('Gamma').click();
            });

        //SubProvider filter
        cy.get('#subprovider-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains('TalkTalk').click();
            });

        //Contact length filter
        cy.get('#contract-length-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains('36').click();
            });

        //Bearer filter
        cy.get('#bearer-multiselect').click();
        cy.wait(500);
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-select-option').contains('1 Gb').click();
            });

        //Speed filter
        cy.get('#speed-multiselect').click();
        cy.wait(500);
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-select-option').contains('500 Mb').click();
            });

        cy.scrollTo('top');
        cy.get('#table-header-speed').click();
        cy.wait(1000);
        cy.get('#table-header-speed').click();
        cy.wait(1000);

        cy.get('table tbody tr.connectivity-table-item')
            .filter(':contains("Internet Access (Wires Only)")')
            .filter(':contains("1 Gb")')
            .filter(':contains("500 Mb")')
            .should('have.length', 2);

        cy.get('tbody tr button').eq(0).click();
        cy.get('#connectivity-product-1').contains('Primary Product');
    });

    it('I can Create an order from a quote', () => {
        cy.visit('/');
        cy.get('#datatable-btn-0-crete-order').click();
        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Order created with success');
    });
});
