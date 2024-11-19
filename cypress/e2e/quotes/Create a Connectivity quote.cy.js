import {generateHash, getCurrentDate} from "../../support/utils.js";

const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login("super@example.com", "werwerwer")
    })

    it("Creates an Account", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#btn-save-account").click()
        cy.get(".notyf").contains("Created with Success")
    })

    it('Create a Connectivity order', () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Connectivity - Ethernet & Fibre")
        cy.get("#input_address").type('EH32 9NT')
        cy.get("#select_address").should('be.visible').select(1)
        cy.get("#btn-create-quote").should('not.be.disabled').click()

        //Provider filter
        cy.get('.p-multiselect').eq(0).click()

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
            cy.get('.p-checkbox').eq(1).click()
        })

        //SubProvider filter
        cy.get('.p-multiselect').eq(1).click()

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
            cy.get('.p-checkbox').eq(1).click()
        })

        cy.get('.cpq-progress-bar').eq(0).should('be.visible')
        cy.wait(3000)

        //Add products to quote
        let keys = [0, 1, 2, 3, 4]
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click()
            cy.get('tbody tr button').eq(key).contains('Added')
        })

        //Save quote
        cy.get('.continue-btn').eq(0).click()
        cy.get('.item-row').its('length').should('eq', 5);
    })

    it('Summary Connectivity order', () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()

        let totals = {
            net: null,
            vat: null,
            gross: null
        }

        Object.keys(totals).forEach((key) => {
            cy.get(`#total_monthly_${key}`).invoke('text').then((text) => {
                totals[key] = text
            })
        })

        //Quote summary
        //First line add 1
        cy.get('#btn-line-0-add-1').click()
        cy.get('input#input-line-0-quantity').then($input => {
            expect($input.val()).to.equal('2');
        });

        cy.wait(1000);
        Object.keys(totals).forEach((key) => {
            cy.get(`#total_monthly_${key}`).invoke('text').then((text) => {
                expect(text).to.not.equal(totals[key])
                totals[key] = text
            })
        })

        //Second line add 2
        cy.get('#btn-line-1-add-1').click().click()
        cy.get('input#input-line-1-quantity').then($input => {
            expect($input.val()).to.equal('3');
        });

        cy.wait(1000);
        Object.keys(totals).forEach((key) => {
            cy.get(`#total_monthly_${key}`).invoke('text').then((text) => {
                expect(text).to.not.equal(totals[key])
                totals[key] = text
            })
        })

        //Third line delete
        cy.get('.item-row').its('length').should('eq', 5);
        cy.get('#delete-btn-3').click()
        cy.get("body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline").click()
        cy.get('.item-row').its('length').should('eq', 4);

        cy.wait(1000);
        Object.keys(totals).forEach((key) => {
            cy.get(`#total_monthly_${key}`).invoke('text').then((text) => {
                expect(text).to.not.equal(totals[key])
            })
        })
    })
})
