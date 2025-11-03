import { faVoteYea } from '@fortawesome/free-solid-svg-icons';
import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp = getCurrentDate();
const provider = 'Giacom';
const subprovider = 'BT';

describe('Quote Approval and Rejection', () => {
    it('Enable Quote Approval', () => {
        cy.login('companyadmin@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-tenant').click();
        cy.get('#1-1').click();
        cy.get('#active-margin-rules> input').then(($checkbox) => {
            if (!$checkbox.is(':checked')) {
                cy.wrap($checkbox).check();
                cy.get('.notyf').contains('Content updated successfully');
            }
        });
        cy.get('#quote-approval-flow').select('margin');
        cy.get('#margin-value').clear().type('99999999');
        cy.get('#margin-operation').select('value');
        cy.wait(2000);
        cy.get('.notyf').contains('Content updated successfully');
    });

    it('Create a Connectivity Quote', () => {
        cy.login('sales@example.com', 'werwerwer');
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

        //Add products to quote
        let keys = [0, 1];
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click();
            cy.get('tbody tr button').eq(key).contains('Added');
        });

        //Save quote
        cy.get('.continue-btn').eq(0).click();
        cy.get('.recurring-item').its('length').should('eq', 2);
        cy.get('.one_off-item').its('length').should('eq', 2);
    });

    it('Start Quote Approval Flow', () => {
        cy.login('sales@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#quote-approval-start').click();
        cy.get('#quote-approval-show-dialog').click();
        cy.get('.quote-approval-row').its('length').should('eq', 2);
    });

    it('Approve Quote made by Sales Rep', () => {
        cy.login('salesmanager@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#quote-approval-show-dialog').click();
        cy.get('.quote-approval-row').its('length').should('eq', 2);
        cy.get('#quote-approval-approve-0').click();
        cy.get('#quote-approval-status-0').contains('Approved');
    });

    it('Send Quote to Order', () => {
        cy.login('sales@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#quote-approval-start').click();
        cy.get('#quote-approval-history').contains('History');
    });

    it('Disable Quote Approval', () => {
        cy.login('companyadmin@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-tenant').click();
        cy.get('#1-1').click();
        cy.get('#active-margin-rules> input').then(($checkbox) => {
            if ($checkbox.is(':checked')) {
                cy.wrap($checkbox).uncheck();
                cy.get('.notyf').contains('Content updated successfully');
            }
        });
    });
});
