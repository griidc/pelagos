package edu.tamucc.hri.griidc.support;

import java.util.*;
import java.io.*;

import javax.mail.*;
import javax.mail.internet.*;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

/**
 * Simple demonstration of using the javax.mail API.
 * 
 * Run from the command line. Please edit the implementation to use correct
 * email addresses and host name.
 */
public final class Emailer {
	
	public static void main(String[] args) {
		Emailer emailer = new Emailer();
		// the domains of these email addresses should be valid,
		// or the example will fail:
		try {
			String[] risErrorLogRecipients = RisToGriidcConfiguration.getRisErrorMsgLogRecipients();
			String[] primaryLogRecipients = RisToGriidcConfiguration.getPrimaryMsgLogRecipients();
			String subject = "GRIIDC email RIS errors test - " + MiscUtils.getDateAndTime();
			emailer.sendEmail("joe.holland@tamucc.edu", risErrorLogRecipients,
					subject, "RIS errors go here");
			subject = "Email GRIIDC log test - " + MiscUtils.getDateAndTime();
			emailer.sendEmail("joe.holland@tamucc.edu", primaryLogRecipients,
					subject, "GRIIDC log messages go here");
			
			System.out.println("IT worked!!!");
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IniSectionNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
	}
	
	

	/**
	 * Send a single email.
	 * @throws FileNotFoundException 
	 * @throws PropertyNotFoundException 
	 * @throws IniSectionNotFoundException 
	 */
	public void sendEmail(String fromEmailAddr, String[] toEmailAddr,
			String subject, String msgBody) throws PropertyNotFoundException, IniSectionNotFoundException {
		// Here, no Authenticator argument is used (it is null).
		// Authenticators are used to prompt the user for user
		// name and password.
		Properties emailConfigProperties = RisToGriidcConfiguration.getEmailProperties();
		Session session = Session.getDefaultInstance(emailConfigProperties, null);
		MimeMessage message = new MimeMessage(session);
		try {
			// the "from" address may be set in code, or set in the
			// config file under "mail.from" ;
			//  if you use message.setFrom() then the config definition is used
			// message.setFrom(new InternetAddress(fromEmailAddr));
			for(String addr : toEmailAddr) {
			message.addRecipient(Message.RecipientType.TO, new InternetAddress(
					addr));
			}
			message.setSubject(subject);
			message.setText(msgBody);
			Transport.send(message);
		} catch (MessagingException ex) {
			System.err.println("Cannot send email. " + ex);
		}
	}
}
