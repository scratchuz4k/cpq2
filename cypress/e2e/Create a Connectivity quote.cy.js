import crypto from "crypto";
import {generateHash, getCurrentDate} from "../support/utils.js";

const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer")
    })

    it("Creates an Account", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#save-account-button").click()
        cy.get(".notyf").contains("Created with Success")
    })
    it('Create a Connectivity order')
})
