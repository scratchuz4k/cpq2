import { generateHash, getCurrentDate } from "../../support/utils.js";

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

        // Scroll back to top
        cy.scrollTo('top');
        //Provider filter
        cy.get('.p-multiselect').eq(0).click()

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
        })

        //SubProvider filter
        cy.get('.p-multiselect').eq(1).click()
        cy.wait(500)

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(1).click()
        })

        //Add products to quote
        let keys = [0, 1, 2, 3, 4]
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click()
            cy.get('tbody tr button').eq(key).contains('Added')
        })

        //Save quote
        cy.get('.continue-btn').eq(0).click()
        cy.get('.recurring-item').its('length').should('eq', 5);
        cy.get('.one_off-item').its('length').should('eq', 5);
    })
    it("I can add a Custom Line and check quoted items", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        cy.get("#btn-recurring-add").click()
        cy.get("#input-new-recurring-name").type("Custom line recurring")
        cy.get("#input-new-recurring-qty").type(10)
        cy.get("#input-new-recurring-price_monthly").type(10)
        cy.get("#input-new-recurring-discount").type(10)
        cy.get("#btn-new-recurring-add").click()
        cy.get("#table-recurring").contains("Custom line recurring")

        cy.get("#btn-one_off-add").click()
        cy.get("#input-new-one_off-name").type("Custom line one_off")
        cy.get("#input-new-one_off-qty").type(10)
        cy.get("#input-new-one_off-price").type(10)
        cy.get("#input-new-one_off-discount").type(10)
        cy.get("#btn-new-one_off-add").click()
        cy.get("#table-one_off").contains("Custom line one_off")

        cy.get("#btn-previous").click()
        cy.get("#btn-more-filters").click()
        cy.get("#filter-show-quoted").click({ force: true })
        cy.wait(5000)
        cy.get('.connectivity-table-item').its("length")
            .then((items) => {
                expect(items).to.equal(5);
            });
    })

    it("I can Create an order from a quote", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-crete-order").click()
        cy.get('.swal2-confirm').eq(0).click()
        cy.get(".notyf").contains("Order created with success")
    })
})
