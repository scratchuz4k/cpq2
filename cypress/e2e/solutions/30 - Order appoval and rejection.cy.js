import { generateHash, getCurrentDate } from "../../support/utils.js";

const temp = getCurrentDate()

describe('Order Approval and Rejection', () => {
    it.skip("Assing Order", () => {
        cy.login("orders@example.com", "werwerwer")
        cy.visit("/")
        cy.get("#datatable-btn-0-assign-order").click()
        cy.get('.swal2-confirm').eq(0).click()
        cy.get(".notyf").contains("Order assigned to you with success")
    })

    it.skip("Approve Order", () => {
        cy.login("orders@example.com", "werwerwer")
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        cy.get("#proceed-order-btn").eq(0).click()
        cy.get('.swal2-cancel').eq(0).click()
        cy.get("#proceed-order-btn").click()
        cy.get(".swal2-confirm").eq(0).click()
    })

    it.skip("Reprove Order", () => {
        cy.login("orders@example.com", "werwerwer")
        cy.visit("/")
        cy.get("#datatable-btn-1-show").click()
        cy.get("#reject-order-btn").eq(0).click()
        cy.get('.swal2-cancel').eq(0).click()
        cy.get("#reject-order-btn").click()
        cy.get(".swal2-confirm").eq(0).click()
        cy.get("#swal2-textarea").type('This is a rejection reason.')
        cy.get(".swal2-confirm").eq(0).click()
    })
})
