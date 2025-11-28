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
        cy.get('#quote-approval-flow').select('Margin review');
        cy.get('#margin-operation').select('value');
        cy.wait(1000);
        cy.get('#margin-value').clear().type('99999');
        cy.wait(1000);
        cy.get('#margin-value').should('have.value', '99999');
        cy.get('.notyf').contains('Content updated successfully');
    });

    it('Creates Mobile Quote', () => {
        cy.login('sales@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectFirstOption('account_id');
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();
        cy.wait(1000);
        cy.get('#btn-plan-0-add-25').click();
        cy.wait(1000);
        cy.get('#btn-plan-1-add-25').click();
        cy.wait(1000);

        cy.get('#current-step')
            .invoke('text')
            .then((currentStepText) => {
                cy.get('#last-step')
                    .invoke('text')
                    .then((lastStepText) => {
                        const current_step = parseInt(currentStepText.trim());
                        const last_step = parseInt(lastStepText.trim());

                        for (let i = current_step; i < last_step; i++) {
                            cy.get(`#btn-step-next`).click();
                        }
                    });
            });

        cy.get('#btn-step-next').click();

        cy.get('.recurring-item').should('have.length', 2);
    });
    it('Start Quote Approval Flow', () => {
        cy.login('sales@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.wait(500);
        cy.get('#quote-approval-start').click();
        cy.get('#quote-approval-show-dialog').click();
        cy.get('.quote-approval-row').its('length').should('eq', 2);
    });

    it('Approve Quote made by Sales Rep', () => {
        cy.login('salesmanager@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.wait(500);
        cy.get('#quote-approval-show-dialog').click();
        cy.get('.quote-approval-row').its('length').should('eq', 2);
        cy.get('#quote-approval-approve-0').click();
        cy.get('.notyf').contains('Quote approved with success!');
    });

    it('Send Quote to Order', () => {
        cy.login('sales@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.wait(500);
        cy.get('#quote-approval-start').click();
        cy.get('.notyf').contains('Quote completed with success!');
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
