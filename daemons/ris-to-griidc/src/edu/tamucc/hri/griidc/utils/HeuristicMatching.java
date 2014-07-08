package edu.tamucc.hri.griidc.utils;

import java.io.FileNotFoundException;
import java.io.IOException;

import org.ini4j.InvalidFileFormatException;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

public class HeuristicMatching {

	private boolean fuzzyPostalCode = false;
	private boolean initialized = false;
	private static boolean  DeBug = false;

	public boolean isFuzzyPostalCode() {
		return fuzzyPostalCode;
	}

	public void setFuzzyPostalCode(boolean fuzzyPostalCode) {
		fuzzyPostalCode = fuzzyPostalCode;
	}

	public HeuristicMatching() {
		// TODO Auto-generated constructor stub
	}

	private void initialize() throws PropertyNotFoundException, InvalidFileFormatException, IOException {
		if (initialized)
			return;
		if(GriidcConfiguration.isFuzzyPostalCodeTrue())
			this.setFuzzyPostalCode(true);
		initialized = true;
	}

	private static int CountryCodeUS = 234; // change to 12345
	private static int CountryCodeCanada = 38; // change to format "A1A 1A1"

	public String fuzzyPostalCode(int countryCode, String postalCode)
			throws PropertyNotFoundException, InvalidFileFormatException, IOException {
		String modifiedPostalCode = postalCode.trim();
		this.initialize();
		if (this.isFuzzyPostalCode()) {
			// if it is us truncate to 5 digits
			if (countryCode == CountryCodeUS) {
				if (modifiedPostalCode.length() > 5) {
					modifiedPostalCode = modifiedPostalCode.substring(0, 5);
					return modifiedPostalCode;
				}
				if(isDeBug()) { System.out.println("modified postal code " + postalCode + " to " + modifiedPostalCode); }
				return modifiedPostalCode;
			} else if (countryCode == CountryCodeCanada) {
				// correct Canadian form
				if (modifiedPostalCode.length() >= 7
						&& modifiedPostalCode.charAt(3) == ' ') {
					return modifiedPostalCode;
				}
				// could be six characters without the embedded space
				if (modifiedPostalCode.length() == 6) {
					StringBuffer sb = new StringBuffer();
					sb.append(modifiedPostalCode.substring(0, 3));
					sb.append(' ');
					sb.append(modifiedPostalCode.substring(3));
					modifiedPostalCode = sb.toString();
					if(isDeBug()) { System.out.println("modified postal code " + postalCode + " to " + modifiedPostalCode); }
					
					return modifiedPostalCode;
				}

			} // else
		}
		return modifiedPostalCode;
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

}
