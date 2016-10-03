# HubSpotContactUpdate
HubSpot API script example. Use this as a template for your own HubSpot contact scripts. 

Updates and cleans a HubSpot contacts, displays an HTML page. The HTML contains processing time, total contact count, and a table of the new contact changes. If the script was unsuccessful, it prints the HTML response status from HubSpot along with what likely caused the problem. 

##Contact Updates: 
1. Set to ALL CAPS: `First/Last name`, `job title`, `company`, `email_2`, `address`, `city`.
2. Format phone numbers as `123-456-7890`.
3. Trim leading and trailing whitespace from these properties.
4. Trim excess whitespace between values (greater than a single space).


##HubSpot HTML responseStatus Codes: 
- **400**:   A property doesn't exist, or a property value is invalid.
- **401**:   Unauthorized request. Check for expired access token or incorrect API key.
- **500**:   Internal Server Error. Something with HubSpot is screwed up and there's probably nothing you can do :(
- **0**:     Could be different things, but check that API calls to this key are coming from one place at a time
 
Example Output:
![Example Output](https://raw.githubusercontent.com/jakewebber/HubSpotContactUpdate/master/screenshot1.png)
