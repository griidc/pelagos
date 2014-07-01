package edu.tamucc.hri.griidc.utils;

import edu.tamucc.hri.griidc.exception.BadCommandLineParmsException;

/**
 * support functions specific to PubsToGriidc
 * 
 * @author jvh
 * 
 */
public class PubsToGriidcUtils {

	public PubsToGriidcUtils() {

	}


	/**
	 * returns a publication serial number or throws
	 * BadCommandLineParmsException
	 * 
	 * @param args
	 * @return
	 * @throws BadCommandLineParmsException
	 */
	public static int processCommandLineArgs(String[] args)
			throws BadCommandLineParmsException {
		int argsLength = args.length;
		String res = "PubsToGriidcUtils test OK";
		if (argsLength != 1) {
			throw new BadCommandLineParmsException(
					"Proper form is one command line arg. The Publication serial number");
		}
		res = args[0]; // the only arg allowed
		if (!isNumericOnly(res)) {
			throw new BadCommandLineParmsException(
					"The command line paramater Publication serial number must contain only numeric characters");
		}
		return Integer.valueOf(res).intValue();
	}
	
	public static boolean isNumericOnly(String s) {
	   int max = s.length();
	   for(int i = 0; i < max; i++) {
		   if(s.charAt(i) < '0' || s.charAt(i) > '9') return false;
	   }
	   return true;
	}

	public static void main(String[] args) {
		String[] testArgs1 = { "1010", "XYZZY" };
		String[] testArgs2 = { "1010XYX" };
		String[] testArgs3 = { "1010" };
		String[][] allTest = { testArgs1, testArgs2, testArgs3 };

		for (int i = 0; i < allTest.length; i++) {
			try {
				int result = PubsToGriidcUtils.processCommandLineArgs(allTest[i]);
				System.out.println(result);
			} catch (BadCommandLineParmsException e) {
				System.err.println("BadCommandLineParmsException: " + e.getMessage());
			}
		}
	}
}
